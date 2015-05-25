using System;
using System.Text;
using System.Runtime.InteropServices;
using System.Windows.Forms;

namespace saf_kmlib
{

    /// <summary>
    /// Standard Keyboard Shortcuts used by most applications
    /// </summary>
    public enum StandardShortcut
    {
        Copy,
        Cut,
        Paste,
        SelectAll,
        Save,
        Open,
        New,
        Close,
        Print
    }

    /// <summary>
    /// Simulate keyboard key presses
    /// </summary>
    public static class KeyboardSimulator
    {

        #region Windows API Code

        const int KEYEVENTF_EXTENDEDKEY = 0x1;
        const int KEYEVENTF_KEYUP = 0x2;

        [DllImport("user32.dll")]
        static extern void keybd_event(byte key, byte scan, int flags, int extraInfo); 

        #endregion

        #region Methods
        static byte getScanCode(Keys key, bool keyup)
        {
            byte code = 0;
            switch (key)
            {
                case Keys.Up:
                    code = 0x48;
                    break;
                case Keys.Down:
                    code = 0x50;
                    break;
                case Keys.PageUp:
                    code = 0x49;
                    break;
                case Keys.PageDown:
                    code = 0x51;
                    break;
                case Keys.Control:
                    code = 0xd1;
                    break;
                case Keys.Alt:
                    code = 0x38;
                    break;
            }
            if (keyup) code += 0x80;
            return code;
        }
        public static void KeyDown(Keys key)
        {
            byte scanCode = getScanCode(key, false);
            int dwExtra = 0;
            dwExtra |= 1; // keycount = 1;
            dwExtra |= (scanCode << 16); //scancode
            dwExtra |= 0x1000; //scancode
            dwExtra = 8080;
            keybd_event(ParseKey(key), scanCode, 0, dwExtra);
        }

        public static void KeyUp(Keys key)
        {
            byte scanCode = getScanCode(key, true);
            int dwExtra = 0;
            dwExtra |= 1; // keycount = 1;
            dwExtra |= (scanCode << 16); //scancode
            dwExtra |= 0x1000; //scancode
            dwExtra = 8080;
            keybd_event(ParseKey(key), scanCode, KEYEVENTF_KEYUP, dwExtra);
        }

        public static void KeyPress(Keys key)
        {
            KeyDown(key);
            System.Threading.Thread.Sleep(150);
            KeyUp(key);
        }

        public static void SimulateStandardShortcut(StandardShortcut shortcut)
        {
            switch (shortcut)
            {
                case StandardShortcut.Copy:
                    KeyDown(Keys.Control);
                    KeyPress(Keys.C);
                    KeyUp(Keys.Control);
                    break;
                case StandardShortcut.Cut:
                    KeyDown(Keys.Control);
                    KeyPress(Keys.X);
                    KeyUp(Keys.Control);
                    break;
                case StandardShortcut.Paste:
                    KeyDown(Keys.Control);
                    KeyPress(Keys.V);
                    KeyUp(Keys.Control);
                    break;
                case StandardShortcut.SelectAll:
                    KeyDown(Keys.Control);
                    KeyPress(Keys.A);
                    KeyUp(Keys.Control);
                    break;
                case StandardShortcut.Save:
                    KeyDown(Keys.Control);
                    KeyPress(Keys.S);
                    KeyUp(Keys.Control);
                    break;
                case StandardShortcut.Open:
                    KeyDown(Keys.Control);
                    KeyPress(Keys.O);
                    KeyUp(Keys.Control);
                    break;
                case StandardShortcut.New:
                    KeyDown(Keys.Control);
                    KeyPress(Keys.N);
                    KeyUp(Keys.Control);
                    break;
                case StandardShortcut.Close:
                    KeyDown(Keys.Alt);
                    KeyPress(Keys.F4);
                    KeyUp(Keys.Alt);
                    break;
                case StandardShortcut.Print:
                    KeyDown(Keys.Control);
                    KeyPress(Keys.P);
                    KeyUp(Keys.Control);
                    break;
            }
        }

        static byte ParseKey(Keys key)
        {

            // Alt, Shift, and Control need to be changed for API function to work with them
            switch (key)
            {
                case Keys.Alt:
                    return (byte)18;
                case Keys.Control:
                    return (byte)17;
                case Keys.Shift:
                    return (byte)16;
                default:
                    return (byte)key;
            }

        } 

        #endregion

    }

}
