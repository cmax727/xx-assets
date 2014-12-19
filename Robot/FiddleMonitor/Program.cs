using System;
using System.Collections.Generic;

using System.Windows.Forms;
using System.Threading;
using System.Diagnostics;
using System.Runtime.InteropServices;

namespace FiddleMonitor
{
    static class Program
    {
        [DllImport("user32.dll")]
        static extern void keybd_event(byte key, byte scan, int flags, int extraInfo); 

        /// <summary>
        /// The main entry point for the application.
        /// </summary>
        [STAThread]
        static void Main()
        {
             bool createdNew = true;
             using (Mutex mtx = new Mutex(true, "sasya8080@gmail.com", out createdNew))
             {

                 if (createdNew)
                 {
                     Application.EnableVisualStyles();
                     Application.SetCompatibleTextRenderingDefault(false);
                     Application.Run(new MacroForm());
                 }
                 else
                 {
                     keybd_event(18, 0, 0, 0);
                     keybd_event(17, 0, 0, 0);
                     keybd_event((byte)Keys.K, 0, 0, 0);
                     keybd_event((byte)Keys.K, 0, 2, 0);
                     keybd_event(18, 0, 2, 0);
                     keybd_event(17, 0, 2, 0);
                 }

             }
        }
    }
}