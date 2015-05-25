using System;
using System.Collections.Generic;
using System.Drawing;
using System.Text;
using System.Windows.Forms;
using saf_kmlib;

using System.Threading;
using System.Runtime.InteropServices;

namespace FiddleMonitor
{
    public partial class MacroForm : Form
    {
        [DllImport("user32.dll")]
        private static extern IntPtr GetForegroundWindow();
        [DllImport("user32.dll")]
        public static extern int SetForegroundWindow(IntPtr hWnd);


        NotifyIcon trayIcon;
        enum MKEvent
        {
            LINE_UP,
            LINE_DOWN,
            MOUSE_CLICK,
            PAGE_UP,
            PAGE_DOWN,
            SWITCH_TAB,
            SWITCH_WIN
        }
        IntPtr thisWnd;
        Queue<MKEvent> events = new Queue<MKEvent>();
        int lastTimeRecorded = Environment.TickCount;
        int minuteCounter = 0;
        bool bKeyFlowStarted = false;
        bool bKeyFlowPaused = false;
        Random rand = new Random();
        int cx = Screen.PrimaryScreen.Bounds.Width / 2;
        int cy = Screen.PrimaryScreen.Bounds.Height / 2;
        bool smallWindow = true;
        MouseHook mouseHook = new MouseHook();
        KeyboardHook keyboardHook = new KeyboardHook();

        public MacroForm()
        {
            InitializeComponent();

            thisWnd = this.Handle;
            Thread oThread = new Thread(new ThreadStart(this.keyPlay));
            oThread.IsBackground = true;
            // Start the thread
            oThread.Start();
            // Spin for a while waiting for the started thread to become
            // alive:
            while (!oThread.IsAlive) ;

            // Put the Main thread to sleep for 1 millisecond to allow oThread
            // to do some work:
            Thread.Sleep(1);

            mouseHook.MouseMove += new MouseEventHandler(mouseHook_MouseMove);
            mouseHook.MouseWheel += new MouseEventHandler(mouseHook_MouseWheel);

            keyboardHook.KeyUp += new KeyEventHandler(keyboardHook_KeyUp);

            mouseHook.Start();
            keyboardHook.Start();
        }

        void mouseHook_MouseMove(object sender, MouseEventArgs e)
        {
            if (sender.ToString() == "8080")
            {
                this.bKeyFlowPaused = true;
            }
            lastTimeRecorded = Environment.TickCount;
        }
        void mouseHook_MouseWheel(object sender, MouseEventArgs e)
        {
            //this.bKeyFlowPaused = true;
        }

        void keyboardHook_KeyUp(object sender, KeyEventArgs e)
        {
            if (sender.ToString() == "8080")
            {
                this.bKeyFlowPaused = true;
                return;
            }
            if (e.KeyCode == Keys.Escape)
            {
                this.bKeyFlowPaused = true;

            }
            if (e.Modifiers == (Keys.Control | Keys.Alt) && e.KeyCode == Keys.K)
            {
                
                this.Show();
                Thread.Sleep(100);
                SetForegroundWindow(this.Handle);
            }
            lastTimeRecorded = Environment.TickCount;
        }
        void startPlay()
        {
            if (datePicker1.Value <= DateTime.Now) return;

            this.bKeyFlowPaused = false;
            this.bKeyFlowStarted = true;
            recordStartButton.Text = "&Stop";
            enqueEvents();
            this.Hide();
        }
        void stopPlay()
        {
            this.bKeyFlowStarted = false;
            recordStartButton.Text = "&Start";
        }
        private void recordStartButton_Click(object sender, EventArgs e)
        {
            if (recordStartButton.Text == "&Start")
            {
                startPlay();
            }
            else
                stopPlay();
        }

        private void recordStopButton_Click(object sender, EventArgs e)
        {
            Thread.Sleep(1000);
            KeyboardSimulator.KeyDown(Keys.Alt);
            KeyboardSimulator.KeyUp(Keys.Alt);
        }

        private void playBackMacroButton_Click(object sender, EventArgs e)
        {
            if (smallWindow)
                this.Height = this.grpBox1.Top - 10 ;
            else
                this.Height = this.label6.Top + this.label6.Height + 80;
            smallWindow = !smallWindow;
        }

        private void notifyIcon1_Click(object sender, EventArgs e)
        {
            SetForegroundWindow(this.Handle);
            this.Show();
            
        }
        System.Drawing.Icon[] icons = {Properties.Resources.main1,Properties.Resources.main2,
            Properties.Resources.main3,Properties.Resources.main4,
            Properties.Resources.main5,Properties.Resources.main6};


        private void MacroForm_Load(object sender, EventArgs e)
        {
            this.Top = 400;
            this.Left = 500;

            this.Icon = icons[rand.Next(0, 5)];

            trayIcon = new NotifyIcon();
            trayIcon.Text = "Fiddler Tracker";
            trayIcon.Icon = Properties.Resources.Icon1;
            trayIcon.Click += new System.EventHandler(this.notifyIcon1_Click);
            // Add menu to tray icon and show it.
            datePicker1.Value = DateTime.Now.AddHours(8);
            trayIcon.Visible = true;
            
            chkKbd_CheckedChanged(null, null);

        }

        private void btnNow_Click(object sender, EventArgs e)
        {
            datePicker1.Value = DateTime.Now;
        }

        private void chkKbd_CheckedChanged(object sender, EventArgs e)
        {
            if (chkKbd.Checked)
            {   // keyboard like job
                txtKmin.Text = "15"; txtKmax.Text = "40";
                txtMmin.Text = "5"; txtMmax.Text = "20";
            }
            else
            {
                txtKmin.Text = "5"; txtKmax.Text = "20";
                txtMmin.Text = "10"; txtMmax.Text = "30";
            }
        }

        private void timer1_Tick(object sender, EventArgs e)
        {
            minuteCounter = (++minuteCounter) % 6;
            enqueEvents();
            changeTime();
        }
        /**
         * enque key events for one minute
         **/
        private void enqueEvents()
        {
            events.Clear();
            Random rand = new Random();

            int tabCnt = rand.Next(1, 30);

            int kmax = (int)txtKmax.Value,
                kmin = (int)txtKmin.Value;
            int mmax = Int16.Parse(txtMmax.Text),
                mmin = Int16.Parse(txtMmin.Text);

            int keyCount = kmin + rand.Next((kmax - kmin) / 3) * 3;
            keyCount = rand.Next(kmin, kmax);
            if (kmin == 0 && keyCount < kmax * .2)
                keyCount = 0;

            int mouseCount = mmin + rand.Next((mmax - mmin) / 3) * 3;
            mouseCount = rand.Next(mmin, mmax);
            if (mmin == 0 && mouseCount < mmax * 0.05)
                mouseCount = 0;
            //MessageBox.Show(keyCount.ToString());
            //MessageBox.Show(mouseCount.ToString());
            // generate key queue
            // one queue is valid for one minute
            /**
             * down flow for 3 minutes, upflow for 3 minutes
             * page down :   5% 
             * key down:     95%
             **/
            MKEvent pgKey = 0,
                arrKey = 0;
            if (minuteCounter < 3)
            {// down flow
                pgKey = MKEvent.PAGE_DOWN;
                arrKey = MKEvent.LINE_DOWN;
            }
            else
            {
                pgKey = MKEvent.PAGE_UP;
                arrKey = MKEvent.LINE_UP;
            }


            // queue Cat play
            // 0th : 
            events.Enqueue(arrKey);
            events.Enqueue(arrKey);
            // 1st : mouse click half times
            for (int i = 0; i < mouseCount / 2 + 1; i++)
            {
                events.Enqueue(MKEvent.MOUSE_CLICK);
            }
            // 2nd : pageup
            int tmp = rand.Next(1, 2);
            while (tmp-- > 0) events.Enqueue(pgKey);

            // 3rd : switch tab
            events.Enqueue(MKEvent.SWITCH_TAB);
            // 4th : siwtch win
            events.Enqueue(MKEvent.SWITCH_WIN);
            // 5th : keyboard half times
            for (int i = 0; i < keyCount / 2; i++)
            {
                events.Enqueue(arrKey);
            }
            // 6th : mouse click for 2nd half times
            for (int i = 0; i < mouseCount / 2 + 1; i++)
            {
                events.Enqueue(MKEvent.MOUSE_CLICK);
            }
            // 8th : keyboard half times 2nd
            for (int i = 0; i < keyCount / 2; i++)
            {
                events.Enqueue(arrKey);
            }

        }
        private IntPtr getThisWnd()
        {
            return this.Handle;
        }
        private void keyPlay()
        {
            var rand = new Random();

            while (true)
            {
                Thread.Sleep(110);
                IntPtr hwndPtr = GetForegroundWindow();

                // 10 seconds of hold off for user's event
                if ((Environment.TickCount - lastTimeRecorded) > 1000 * 10) bKeyFlowPaused = false;
                if (datePicker1.Value.Ticks < DateTime.Now.Ticks)
                {
                    bKeyFlowStarted = false;
                }

                if (!bKeyFlowStarted || bKeyFlowPaused)
                    continue;

                if (hwndPtr == thisWnd) continue;

                int winCount = Int16.Parse(txtWincount.Text);

                MKEvent mkevent = 0;
                if (events.Count > 0)
                {
                    mkevent = events.Dequeue();
                    Thread.Sleep(rand.Next(100, 500));
                    if (mkevent != MKEvent.MOUSE_CLICK)
                    {
                        //continue;
                    }

                    switch (mkevent)
                    {
                        case MKEvent.MOUSE_CLICK:
                            if (chkMousePos.Checked)
                            {
                                int mx = cx + rand.Next(80) - 40;
                                int my = cy + rand.Next(80) - 40;


                                Thread.Sleep(50);
                                MouseSimulator.MouseMove(mx, my);
                                MouseSimulator.MouseDown(MouseButton.Left);
                                //MouseSimulator.MouseMove(mx + 1, my + 1);
                                Thread.Sleep(100);
                                //MouseSimulator.MouseMove(mx - 100, my - 100);
                                Thread.Sleep(100);
                                //MouseSimulator.MouseMove(mx, my);
                                Thread.Sleep(100);
                                MouseSimulator.MouseUp(MouseButton.Left);
                            }
                            else
                            {
                                MouseSimulator.MouseDown(MouseButton.Left);
                                Thread.Sleep(300);
                                MouseSimulator.MouseUp(MouseButton.Left);
                            }
                            break;
                        case MKEvent.LINE_UP:
                            {
                                KeyboardSimulator.KeyPress(Keys.Up);
                            }
                            break;
                        case MKEvent.LINE_DOWN:
                            {
                                KeyboardSimulator.KeyPress(Keys.Down);
                            }
                            break;
                        case MKEvent.PAGE_UP:
                            {
                                KeyboardSimulator.KeyPress(Keys.PageUp);
                            }
                            break;
                        case MKEvent.PAGE_DOWN:
                            {
                                KeyboardSimulator.KeyPress(Keys.PageDown);
                            }
                            break;
                        case MKEvent.SWITCH_TAB:
                            KeyboardSimulator.KeyDown(Keys.LControlKey);
                            {
                                int tmp = rand.Next(1, 15);
                                while (tmp-- > 0)
                                {
                                    KeyboardSimulator.KeyPress(Keys.Tab);
                                    Thread.Sleep(100);
                                }
                            } KeyboardSimulator.KeyUp(Keys.LControlKey);
                            break;
                        case MKEvent.SWITCH_WIN:
                            {
                                int tmp = rand.Next(1, Math.Max(winCount, 1));
                                if (tmp > 1)
                                {
                                    KeyboardSimulator.KeyDown(Keys.Alt);
                                    while (--tmp > 0)
                                    {
                                        KeyboardSimulator.KeyPress(Keys.Tab);
                                        Thread.Sleep(100);
                                    }
                                    KeyboardSimulator.KeyUp(Keys.Alt);
                                }
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
        }

        private void datePicker1_ValueChanged(object sender, EventArgs e)
        {
            changeTime();
        }
        private void changeTime()
        {
            TimeSpan ts = (datePicker1.Value - DateTime.Now + TimeSpan.FromMinutes(1));
            lblTimeLeft.Text = (ts.Days * 24 + ts.Hours) + " : " + ts.Minutes;
        }

        private void button1_Click(object sender, EventArgs e)
        {
            trayIcon.Visible = false;
            Environment.Exit(0);
        }

        private void MacroForm_Deactivate(object sender, EventArgs e)
        {
            this.Hide();
        }

        private void statusChecker_Tick(object sender, EventArgs e)
        {
            if (this.bKeyFlowStarted && !this.bKeyFlowPaused)
                trayIcon.Icon = Properties.Resources.Icon1;
            else if (this.bKeyFlowStarted && this.bKeyFlowPaused)
                trayIcon.Icon = Properties.Resources.Icon2;
            else
            {
                trayIcon.Icon = Properties.Resources.Icon3;
                recordStartButton.Text = "&Start";
            }
            if ((Environment.TickCount - lastTimeRecorded) > 1000 * 10)
            {// 10 sec lasted, then hide
                this.Hide();
            }
            pictureBox1.Image = trayIcon.Icon.ToBitmap();

        }

        private void MacroForm_FormClosed(object sender, FormClosedEventArgs e)
        {
            trayIcon.Visible = false;

        }

        private void MacroForm_FormClosing(object sender, FormClosingEventArgs e)
        {
            e.Cancel = true;
            this.Hide();

        }

        private void label4_Click(object sender, EventArgs e)
        {

        }

        private void MacroForm_Activated(object sender, EventArgs e)
        {
            this.smallWindow = true;
            playBackMacroButton_Click(null, null);
            this.Icon = icons[rand.Next(0, 6)];

        }
    }
}
