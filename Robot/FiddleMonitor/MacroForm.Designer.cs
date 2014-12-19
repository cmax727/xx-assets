namespace FiddleMonitor
{
    partial class MacroForm
    {
        /// <summary>
        /// Required designer variable.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary>
        /// Clean up any resources being used.
        /// </summary>
        /// <param name="disposing">true if managed resources should be disposed; otherwise, false.</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Windows Form Designer generated code

        /// <summary>
        /// Required method for Designer support - do not modify
        /// the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            this.components = new System.ComponentModel.Container();
            System.ComponentModel.ComponentResourceManager resources = new System.ComponentModel.ComponentResourceManager(typeof(MacroForm));
            this.recordStartButton = new System.Windows.Forms.Button();
            this.playBackMacroButton = new System.Windows.Forms.Button();
            this.datePicker1 = new System.Windows.Forms.DateTimePicker();
            this.grpBox1 = new System.Windows.Forms.GroupBox();
            this.chkMousePos = new System.Windows.Forms.CheckBox();
            this.txtWincount = new System.Windows.Forms.NumericUpDown();
            this.txtMmax = new System.Windows.Forms.NumericUpDown();
            this.label1 = new System.Windows.Forms.Label();
            this.txtMmin = new System.Windows.Forms.NumericUpDown();
            this.txtKmax = new System.Windows.Forms.NumericUpDown();
            this.txtKmin = new System.Windows.Forms.NumericUpDown();
            this.label5 = new System.Windows.Forms.Label();
            this.chkKbd = new System.Windows.Forms.RadioButton();
            this.chkMouse = new System.Windows.Forms.RadioButton();
            this.label4 = new System.Windows.Forms.Label();
            this.label3 = new System.Windows.Forms.Label();
            this.label6 = new System.Windows.Forms.Label();
            this.timer1 = new System.Windows.Forms.Timer(this.components);
            this.label7 = new System.Windows.Forms.Label();
            this.lblTimeLeft = new System.Windows.Forms.Label();
            this.button1 = new System.Windows.Forms.Button();
            this.statusChecker = new System.Windows.Forms.Timer(this.components);
            this.pictureBox1 = new System.Windows.Forms.PictureBox();
            this.tableLayoutPanel1 = new System.Windows.Forms.TableLayoutPanel();
            this.tableLayoutPanel2 = new System.Windows.Forms.TableLayoutPanel();
            this.tableLayoutPanel3 = new System.Windows.Forms.TableLayoutPanel();
            this.flowLayoutPanel1 = new System.Windows.Forms.FlowLayoutPanel();
            this.btnNow = new System.Windows.Forms.Button();
            this.grpBox1.SuspendLayout();
            ((System.ComponentModel.ISupportInitialize)(this.txtWincount)).BeginInit();
            ((System.ComponentModel.ISupportInitialize)(this.txtMmax)).BeginInit();
            ((System.ComponentModel.ISupportInitialize)(this.txtMmin)).BeginInit();
            ((System.ComponentModel.ISupportInitialize)(this.txtKmax)).BeginInit();
            ((System.ComponentModel.ISupportInitialize)(this.txtKmin)).BeginInit();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox1)).BeginInit();
            this.tableLayoutPanel1.SuspendLayout();
            this.tableLayoutPanel2.SuspendLayout();
            this.tableLayoutPanel3.SuspendLayout();
            this.flowLayoutPanel1.SuspendLayout();
            this.SuspendLayout();
            // 
            // recordStartButton
            // 
            this.recordStartButton.Anchor = ((System.Windows.Forms.AnchorStyles)(((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Left)
                        | System.Windows.Forms.AnchorStyles.Right)));
            this.recordStartButton.AutoSizeMode = System.Windows.Forms.AutoSizeMode.GrowAndShrink;
            this.recordStartButton.BackColor = System.Drawing.Color.Brown;
            this.recordStartButton.ForeColor = System.Drawing.Color.DarkKhaki;
            this.recordStartButton.Location = new System.Drawing.Point(4, 4);
            this.recordStartButton.Margin = new System.Windows.Forms.Padding(4);
            this.recordStartButton.Name = "recordStartButton";
            this.recordStartButton.Size = new System.Drawing.Size(207, 28);
            this.recordStartButton.TabIndex = 2;
            this.recordStartButton.Text = "&Start";
            this.recordStartButton.UseVisualStyleBackColor = false;
            this.recordStartButton.Click += new System.EventHandler(this.recordStartButton_Click);
            // 
            // playBackMacroButton
            // 
            this.playBackMacroButton.Anchor = ((System.Windows.Forms.AnchorStyles)(((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Left)
                        | System.Windows.Forms.AnchorStyles.Right)));
            this.playBackMacroButton.AutoSizeMode = System.Windows.Forms.AutoSizeMode.GrowAndShrink;
            this.playBackMacroButton.BackColor = System.Drawing.Color.Brown;
            this.playBackMacroButton.ForeColor = System.Drawing.Color.DarkKhaki;
            this.playBackMacroButton.Location = new System.Drawing.Point(4, 46);
            this.playBackMacroButton.Margin = new System.Windows.Forms.Padding(4);
            this.playBackMacroButton.Name = "playBackMacroButton";
            this.playBackMacroButton.Size = new System.Drawing.Size(207, 28);
            this.playBackMacroButton.TabIndex = 3;
            this.playBackMacroButton.Text = "Fold / Expand";
            this.playBackMacroButton.UseVisualStyleBackColor = false;
            this.playBackMacroButton.Click += new System.EventHandler(this.playBackMacroButton_Click);
            // 
            // datePicker1
            // 
            this.datePicker1.CustomFormat = "yyyy-MM-dd  HH:mm";
            this.datePicker1.Format = System.Windows.Forms.DateTimePickerFormat.Custom;
            this.datePicker1.Location = new System.Drawing.Point(3, 3);
            this.datePicker1.Name = "datePicker1";
            this.datePicker1.Size = new System.Drawing.Size(208, 22);
            this.datePicker1.TabIndex = 0;
            this.datePicker1.ValueChanged += new System.EventHandler(this.datePicker1_ValueChanged);
            // 
            // grpBox1
            // 
            this.grpBox1.Anchor = ((System.Windows.Forms.AnchorStyles)(((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Left)
                        | System.Windows.Forms.AnchorStyles.Right)));
            this.grpBox1.BackColor = System.Drawing.Color.Transparent;
            this.grpBox1.Controls.Add(this.chkMousePos);
            this.grpBox1.Controls.Add(this.txtWincount);
            this.grpBox1.Controls.Add(this.txtMmax);
            this.grpBox1.Controls.Add(this.label1);
            this.grpBox1.Controls.Add(this.txtMmin);
            this.grpBox1.Controls.Add(this.txtKmax);
            this.grpBox1.Controls.Add(this.txtKmin);
            this.grpBox1.Controls.Add(this.label5);
            this.grpBox1.Controls.Add(this.chkKbd);
            this.grpBox1.Controls.Add(this.chkMouse);
            this.grpBox1.Controls.Add(this.label4);
            this.grpBox1.Controls.Add(this.label3);
            this.grpBox1.ForeColor = System.Drawing.Color.White;
            this.grpBox1.Location = new System.Drawing.Point(3, 158);
            this.grpBox1.Name = "grpBox1";
            this.grpBox1.Size = new System.Drawing.Size(424, 174);
            this.grpBox1.TabIndex = 8;
            this.grpBox1.TabStop = false;
            this.grpBox1.Text = "Settings";
            // 
            // chkMousePos
            // 
            this.chkMousePos.AutoSize = true;
            this.chkMousePos.Checked = true;
            this.chkMousePos.CheckState = System.Windows.Forms.CheckState.Checked;
            this.chkMousePos.Location = new System.Drawing.Point(295, 24);
            this.chkMousePos.Name = "chkMousePos";
            this.chkMousePos.Size = new System.Drawing.Size(54, 21);
            this.chkMousePos.TabIndex = 21;
            this.chkMousePos.Text = "A.M";
            this.chkMousePos.UseVisualStyleBackColor = true;
            // 
            // txtWincount
            // 
            this.txtWincount.Location = new System.Drawing.Point(165, 21);
            this.txtWincount.Maximum = new decimal(new int[] {
            200,
            0,
            0,
            0});
            this.txtWincount.Name = "txtWincount";
            this.txtWincount.Size = new System.Drawing.Size(51, 22);
            this.txtWincount.TabIndex = 18;
            // 
            // txtMmax
            // 
            this.txtMmax.Location = new System.Drawing.Point(278, 124);
            this.txtMmax.Maximum = new decimal(new int[] {
            200,
            0,
            0,
            0});
            this.txtMmax.Name = "txtMmax";
            this.txtMmax.Size = new System.Drawing.Size(51, 22);
            this.txtMmax.TabIndex = 20;
            this.txtMmax.Value = new decimal(new int[] {
            20,
            0,
            0,
            0});
            // 
            // label1
            // 
            this.label1.AutoSize = true;
            this.label1.BackColor = System.Drawing.Color.Transparent;
            this.label1.ForeColor = System.Drawing.Color.YellowGreen;
            this.label1.Location = new System.Drawing.Point(6, 48);
            this.label1.Name = "label1";
            this.label1.Size = new System.Drawing.Size(423, 17);
            this.label1.TabIndex = 14;
            this.label1.Text = "---------------------------------------------------------------------------------" +
                "--";
            // 
            // txtMmin
            // 
            this.txtMmin.Location = new System.Drawing.Point(201, 124);
            this.txtMmin.Maximum = new decimal(new int[] {
            200,
            0,
            0,
            0});
            this.txtMmin.Name = "txtMmin";
            this.txtMmin.Size = new System.Drawing.Size(51, 22);
            this.txtMmin.TabIndex = 19;
            this.txtMmin.Value = new decimal(new int[] {
            5,
            0,
            0,
            0});
            // 
            // txtKmax
            // 
            this.txtKmax.Location = new System.Drawing.Point(278, 93);
            this.txtKmax.Maximum = new decimal(new int[] {
            200,
            0,
            0,
            0});
            this.txtKmax.Name = "txtKmax";
            this.txtKmax.Size = new System.Drawing.Size(51, 22);
            this.txtKmax.TabIndex = 18;
            this.txtKmax.Value = new decimal(new int[] {
            50,
            0,
            0,
            0});
            // 
            // txtKmin
            // 
            this.txtKmin.Location = new System.Drawing.Point(201, 93);
            this.txtKmin.Maximum = new decimal(new int[] {
            200,
            0,
            0,
            0});
            this.txtKmin.Name = "txtKmin";
            this.txtKmin.Size = new System.Drawing.Size(51, 22);
            this.txtKmin.TabIndex = 17;
            this.txtKmin.Value = new decimal(new int[] {
            11,
            0,
            0,
            0});
            // 
            // label5
            // 
            this.label5.AutoSize = true;
            this.label5.ForeColor = System.Drawing.Color.White;
            this.label5.Location = new System.Drawing.Point(68, 25);
            this.label5.Margin = new System.Windows.Forms.Padding(4, 0, 4, 0);
            this.label5.Name = "label5";
            this.label5.Size = new System.Drawing.Size(68, 17);
            this.label5.TabIndex = 15;
            this.label5.Text = "Windows:";
            // 
            // chkKbd
            // 
            this.chkKbd.AutoSize = true;
            this.chkKbd.Checked = true;
            this.chkKbd.ForeColor = System.Drawing.Color.White;
            this.chkKbd.Location = new System.Drawing.Point(54, 91);
            this.chkKbd.Name = "chkKbd";
            this.chkKbd.Size = new System.Drawing.Size(116, 21);
            this.chkKbd.TabIndex = 14;
            this.chkKbd.TabStop = true;
            this.chkKbd.Text = "Key preferred";
            this.chkKbd.UseVisualStyleBackColor = true;
            this.chkKbd.CheckedChanged += new System.EventHandler(this.chkKbd_CheckedChanged);
            // 
            // chkMouse
            // 
            this.chkMouse.AutoSize = true;
            this.chkMouse.ForeColor = System.Drawing.Color.White;
            this.chkMouse.Location = new System.Drawing.Point(54, 123);
            this.chkMouse.Name = "chkMouse";
            this.chkMouse.Size = new System.Drawing.Size(134, 21);
            this.chkMouse.TabIndex = 14;
            this.chkMouse.Text = "Mouse preferred";
            this.chkMouse.UseVisualStyleBackColor = true;
            this.chkMouse.CheckedChanged += new System.EventHandler(this.chkKbd_CheckedChanged);
            // 
            // label4
            // 
            this.label4.AutoSize = true;
            this.label4.ForeColor = System.Drawing.Color.White;
            this.label4.Location = new System.Drawing.Point(201, 65);
            this.label4.Margin = new System.Windows.Forms.Padding(4, 0, 4, 0);
            this.label4.Name = "label4";
            this.label4.Size = new System.Drawing.Size(34, 17);
            this.label4.TabIndex = 8;
            this.label4.Text = "min ";
            this.label4.Click += new System.EventHandler(this.label4_Click);
            // 
            // label3
            // 
            this.label3.AutoSize = true;
            this.label3.ForeColor = System.Drawing.Color.White;
            this.label3.Location = new System.Drawing.Point(278, 65);
            this.label3.Margin = new System.Windows.Forms.Padding(4, 0, 4, 0);
            this.label3.Name = "label3";
            this.label3.Size = new System.Drawing.Size(33, 17);
            this.label3.TabIndex = 10;
            this.label3.Text = "max";
            // 
            // label6
            // 
            this.label6.Anchor = ((System.Windows.Forms.AnchorStyles)((System.Windows.Forms.AnchorStyles.Left | System.Windows.Forms.AnchorStyles.Right)));
            this.label6.BackColor = System.Drawing.Color.Transparent;
            this.label6.ForeColor = System.Drawing.Color.Yellow;
            this.label6.Location = new System.Drawing.Point(0, 336);
            this.label6.Margin = new System.Windows.Forms.Padding(0);
            this.label6.Name = "label6";
            this.label6.Size = new System.Drawing.Size(430, 17);
            this.label6.TabIndex = 14;
            this.label6.Text = "Author: SAF<sasya8080@gmail.com>, Copyright © 2018";
            // 
            // timer1
            // 
            this.timer1.Enabled = true;
            this.timer1.Interval = 60000;
            this.timer1.Tick += new System.EventHandler(this.timer1_Tick);
            // 
            // label7
            // 
            this.label7.Anchor = ((System.Windows.Forms.AnchorStyles)(((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Left)
                        | System.Windows.Forms.AnchorStyles.Right)));
            this.label7.AutoSize = true;
            this.label7.BackColor = System.Drawing.Color.Transparent;
            this.label7.ForeColor = System.Drawing.Color.White;
            this.label7.Location = new System.Drawing.Point(4, 0);
            this.label7.Margin = new System.Windows.Forms.Padding(4, 0, 4, 0);
            this.label7.Name = "label7";
            this.label7.Size = new System.Drawing.Size(71, 17);
            this.label7.TabIndex = 2;
            this.label7.Text = "Time Left:";
            // 
            // lblTimeLeft
            // 
            this.lblTimeLeft.Anchor = ((System.Windows.Forms.AnchorStyles)(((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Left)
                        | System.Windows.Forms.AnchorStyles.Right)));
            this.lblTimeLeft.AutoSize = true;
            this.lblTimeLeft.BackColor = System.Drawing.Color.Transparent;
            this.lblTimeLeft.ForeColor = System.Drawing.Color.White;
            this.lblTimeLeft.Location = new System.Drawing.Point(83, 0);
            this.lblTimeLeft.Margin = new System.Windows.Forms.Padding(4, 0, 4, 0);
            this.lblTimeLeft.Name = "lblTimeLeft";
            this.lblTimeLeft.Size = new System.Drawing.Size(36, 17);
            this.lblTimeLeft.TabIndex = 2;
            this.lblTimeLeft.Text = "0 : 0";
            // 
            // button1
            // 
            this.button1.Anchor = ((System.Windows.Forms.AnchorStyles)((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Right)));
            this.button1.AutoSizeMode = System.Windows.Forms.AutoSizeMode.GrowAndShrink;
            this.button1.BackColor = System.Drawing.Color.Brown;
            this.button1.ForeColor = System.Drawing.Color.DarkKhaki;
            this.button1.Location = new System.Drawing.Point(312, 45);
            this.button1.Name = "button1";
            this.button1.Size = new System.Drawing.Size(115, 28);
            this.button1.TabIndex = 4;
            this.button1.Text = "E&xit";
            this.button1.UseVisualStyleBackColor = false;
            this.button1.Click += new System.EventHandler(this.button1_Click);
            // 
            // statusChecker
            // 
            this.statusChecker.Enabled = true;
            this.statusChecker.Interval = 1000;
            this.statusChecker.Tick += new System.EventHandler(this.statusChecker_Tick);
            // 
            // pictureBox1
            // 
            this.pictureBox1.Anchor = ((System.Windows.Forms.AnchorStyles)((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Right)));
            this.pictureBox1.BackColor = System.Drawing.Color.Transparent;
            this.pictureBox1.Location = new System.Drawing.Point(372, 3);
            this.pictureBox1.Name = "pictureBox1";
            this.pictureBox1.Size = new System.Drawing.Size(55, 36);
            this.pictureBox1.TabIndex = 16;
            this.pictureBox1.TabStop = false;
            // 
            // tableLayoutPanel1
            // 
            this.tableLayoutPanel1.Anchor = ((System.Windows.Forms.AnchorStyles)(((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Left)
                        | System.Windows.Forms.AnchorStyles.Right)));
            this.tableLayoutPanel1.BackColor = System.Drawing.Color.Transparent;
            this.tableLayoutPanel1.ColumnCount = 1;
            this.tableLayoutPanel1.ColumnStyles.Add(new System.Windows.Forms.ColumnStyle(System.Windows.Forms.SizeType.Percent, 50.69767F));
            this.tableLayoutPanel1.Controls.Add(this.grpBox1, 0, 2);
            this.tableLayoutPanel1.Controls.Add(this.tableLayoutPanel2, 0, 1);
            this.tableLayoutPanel1.Controls.Add(this.tableLayoutPanel3, 0, 0);
            this.tableLayoutPanel1.Controls.Add(this.label6, 0, 3);
            this.tableLayoutPanel1.Location = new System.Drawing.Point(12, 12);
            this.tableLayoutPanel1.Name = "tableLayoutPanel1";
            this.tableLayoutPanel1.RowCount = 4;
            this.tableLayoutPanel1.RowStyles.Add(new System.Windows.Forms.RowStyle(System.Windows.Forms.SizeType.Percent, 50F));
            this.tableLayoutPanel1.RowStyles.Add(new System.Windows.Forms.RowStyle(System.Windows.Forms.SizeType.Absolute, 94F));
            this.tableLayoutPanel1.RowStyles.Add(new System.Windows.Forms.RowStyle(System.Windows.Forms.SizeType.Absolute, 180F));
            this.tableLayoutPanel1.RowStyles.Add(new System.Windows.Forms.RowStyle(System.Windows.Forms.SizeType.Absolute, 20F));
            this.tableLayoutPanel1.Size = new System.Drawing.Size(430, 355);
            this.tableLayoutPanel1.TabIndex = 17;
            // 
            // tableLayoutPanel2
            // 
            this.tableLayoutPanel2.ColumnCount = 2;
            this.tableLayoutPanel2.ColumnStyles.Add(new System.Windows.Forms.ColumnStyle(System.Windows.Forms.SizeType.Percent, 50F));
            this.tableLayoutPanel2.ColumnStyles.Add(new System.Windows.Forms.ColumnStyle(System.Windows.Forms.SizeType.Percent, 50F));
            this.tableLayoutPanel2.Controls.Add(this.recordStartButton, 0, 0);
            this.tableLayoutPanel2.Controls.Add(this.button1, 1, 1);
            this.tableLayoutPanel2.Controls.Add(this.playBackMacroButton, 0, 1);
            this.tableLayoutPanel2.Controls.Add(this.pictureBox1, 1, 0);
            this.tableLayoutPanel2.Location = new System.Drawing.Point(0, 61);
            this.tableLayoutPanel2.Margin = new System.Windows.Forms.Padding(0);
            this.tableLayoutPanel2.Name = "tableLayoutPanel2";
            this.tableLayoutPanel2.RowCount = 2;
            this.tableLayoutPanel2.RowStyles.Add(new System.Windows.Forms.RowStyle(System.Windows.Forms.SizeType.Percent, 45.74468F));
            this.tableLayoutPanel2.RowStyles.Add(new System.Windows.Forms.RowStyle(System.Windows.Forms.SizeType.Percent, 54.25532F));
            this.tableLayoutPanel2.Size = new System.Drawing.Size(430, 94);
            this.tableLayoutPanel2.TabIndex = 18;
            // 
            // tableLayoutPanel3
            // 
            this.tableLayoutPanel3.ColumnCount = 2;
            this.tableLayoutPanel3.ColumnStyles.Add(new System.Windows.Forms.ColumnStyle(System.Windows.Forms.SizeType.Percent, 72.36534F));
            this.tableLayoutPanel3.ColumnStyles.Add(new System.Windows.Forms.ColumnStyle(System.Windows.Forms.SizeType.Percent, 27.63466F));
            this.tableLayoutPanel3.Controls.Add(this.datePicker1, 0, 0);
            this.tableLayoutPanel3.Controls.Add(this.flowLayoutPanel1, 0, 1);
            this.tableLayoutPanel3.Controls.Add(this.btnNow, 1, 0);
            this.tableLayoutPanel3.Location = new System.Drawing.Point(0, 0);
            this.tableLayoutPanel3.Margin = new System.Windows.Forms.Padding(0);
            this.tableLayoutPanel3.Name = "tableLayoutPanel3";
            this.tableLayoutPanel3.RowCount = 2;
            this.tableLayoutPanel3.RowStyles.Add(new System.Windows.Forms.RowStyle(System.Windows.Forms.SizeType.Percent, 59.01639F));
            this.tableLayoutPanel3.RowStyles.Add(new System.Windows.Forms.RowStyle(System.Windows.Forms.SizeType.Percent, 40.98361F));
            this.tableLayoutPanel3.Size = new System.Drawing.Size(427, 61);
            this.tableLayoutPanel3.TabIndex = 19;
            // 
            // flowLayoutPanel1
            // 
            this.flowLayoutPanel1.Controls.Add(this.label7);
            this.flowLayoutPanel1.Controls.Add(this.lblTimeLeft);
            this.flowLayoutPanel1.Location = new System.Drawing.Point(0, 35);
            this.flowLayoutPanel1.Margin = new System.Windows.Forms.Padding(0);
            this.flowLayoutPanel1.Name = "flowLayoutPanel1";
            this.flowLayoutPanel1.Size = new System.Drawing.Size(200, 26);
            this.flowLayoutPanel1.TabIndex = 1;
            // 
            // btnNow
            // 
            this.btnNow.Anchor = ((System.Windows.Forms.AnchorStyles)((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Right)));
            this.btnNow.AutoSizeMode = System.Windows.Forms.AutoSizeMode.GrowAndShrink;
            this.btnNow.BackColor = System.Drawing.Color.Brown;
            this.btnNow.ForeColor = System.Drawing.Color.DarkKhaki;
            this.btnNow.Location = new System.Drawing.Point(312, 3);
            this.btnNow.Name = "btnNow";
            this.btnNow.Size = new System.Drawing.Size(112, 28);
            this.btnNow.TabIndex = 1;
            this.btnNow.Text = "Now";
            this.btnNow.UseVisualStyleBackColor = false;
            this.btnNow.Click += new System.EventHandler(this.btnNow_Click);
            // 
            // MacroForm
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(8F, 16F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.BackgroundImage = global::FiddleMonitor.Properties.Resources.img_bk;
            this.ClientSize = new System.Drawing.Size(452, 376);
            this.Controls.Add(this.tableLayoutPanel1);
            this.DoubleBuffered = true;
            this.FormBorderStyle = System.Windows.Forms.FormBorderStyle.FixedSingle;
            this.Icon = ((System.Drawing.Icon)(resources.GetObject("$this.Icon")));
            this.Margin = new System.Windows.Forms.Padding(4);
            this.MaximumSize = new System.Drawing.Size(458, 409);
            this.MinimumSize = new System.Drawing.Size(458, 190);
            this.Name = "MacroForm";
            this.Text = "Fiddle Monitor ©SAF";
            this.Activated += new System.EventHandler(this.MacroForm_Activated);
            this.Deactivate += new System.EventHandler(this.MacroForm_Deactivate);
            this.FormClosing += new System.Windows.Forms.FormClosingEventHandler(this.MacroForm_FormClosing);
            this.FormClosed += new System.Windows.Forms.FormClosedEventHandler(this.MacroForm_FormClosed);
            this.Load += new System.EventHandler(this.MacroForm_Load);
            this.grpBox1.ResumeLayout(false);
            this.grpBox1.PerformLayout();
            ((System.ComponentModel.ISupportInitialize)(this.txtWincount)).EndInit();
            ((System.ComponentModel.ISupportInitialize)(this.txtMmax)).EndInit();
            ((System.ComponentModel.ISupportInitialize)(this.txtMmin)).EndInit();
            ((System.ComponentModel.ISupportInitialize)(this.txtKmax)).EndInit();
            ((System.ComponentModel.ISupportInitialize)(this.txtKmin)).EndInit();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox1)).EndInit();
            this.tableLayoutPanel1.ResumeLayout(false);
            this.tableLayoutPanel2.ResumeLayout(false);
            this.tableLayoutPanel3.ResumeLayout(false);
            this.flowLayoutPanel1.ResumeLayout(false);
            this.flowLayoutPanel1.PerformLayout();
            this.ResumeLayout(false);

        }

        #endregion

        private System.Windows.Forms.Button recordStartButton;
        private System.Windows.Forms.Button playBackMacroButton;
        private System.Windows.Forms.DateTimePicker datePicker1;
        private System.Windows.Forms.GroupBox grpBox1;
        private System.Windows.Forms.Label label4;
        private System.Windows.Forms.Label label3;
        private System.Windows.Forms.Label label6;
        private System.Windows.Forms.RadioButton chkKbd;
        private System.Windows.Forms.RadioButton chkMouse;
        private System.Windows.Forms.Label label5;
        private System.Windows.Forms.Timer timer1;
        private System.Windows.Forms.Label label7;
        private System.Windows.Forms.Label lblTimeLeft;
        private System.Windows.Forms.Button button1;
        private System.Windows.Forms.Timer statusChecker;
        private System.Windows.Forms.PictureBox pictureBox1;
        private System.Windows.Forms.NumericUpDown txtKmin;
        private System.Windows.Forms.NumericUpDown txtMmax;
        private System.Windows.Forms.NumericUpDown txtMmin;
        private System.Windows.Forms.NumericUpDown txtKmax;
        private System.Windows.Forms.NumericUpDown txtWincount;
        private System.Windows.Forms.Label label1;
        private System.Windows.Forms.TableLayoutPanel tableLayoutPanel1;
        private System.Windows.Forms.TableLayoutPanel tableLayoutPanel2;
        private System.Windows.Forms.Button btnNow;
        private System.Windows.Forms.TableLayoutPanel tableLayoutPanel3;
        private System.Windows.Forms.FlowLayoutPanel flowLayoutPanel1;
        private System.Windows.Forms.CheckBox chkMousePos;
    }
}

