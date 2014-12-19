package script.cmd;

import script.LineCmd;

public class Cmd_NOP extends LineCmd{

	public Cmd_NOP(String szCmdName) {
		super(szCmdName);
		// TODO Auto-generated constructor stub
	}

	@Override
	protected boolean __execute() {
		// that's all
		return true;
	}

}
