package script.cmd;

import script.LineCmd;
import script.LinearInterpreter;

public class Cmd_UNIMPLEMENTED extends LineCmd{

	public Cmd_UNIMPLEMENTED(String szCmdName) {
		super(szCmdName);
		// TODO Auto-generated constructor stub
	}

	@Override
	protected boolean __execute() {
		// nothing to do
		return false;
	}

}
