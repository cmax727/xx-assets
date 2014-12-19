package script.cmd;

import script.LineCmd;



public class CmdPrint extends LineCmd{

	public CmdPrint(String szName) {
		super(szName);
		// TODO Auto-generated constructor stub
	}

	@Override
	protected boolean __execute() {
		System.out.println(this.paramStr);
		return true;
	}

}
