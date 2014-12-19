// installhook.cpp : Defines the class behaviors for the application.
//

#include "stdafx.h"
#include "installhook.h"
#include "installhookDlg.h"

#ifdef _DEBUG
#define new DEBUG_NEW
#undef THIS_FILE
static char THIS_FILE[] = __FILE__;
#endif

/////////////////////////////////////////////////////////////////////////////
// CInstallhookApp

BEGIN_MESSAGE_MAP(CInstallhookApp, CWinApp)
	//{{AFX_MSG_MAP(CInstallhookApp)
	//}}AFX_MSG
	ON_COMMAND(ID_HELP, CWinApp::OnHelp)
END_MESSAGE_MAP()

/////////////////////////////////////////////////////////////////////////////
// CInstallhookApp construction

CInstallhookApp::CInstallhookApp()
{
}

/////////////////////////////////////////////////////////////////////////////
// The one and only CInstallhookApp object

CInstallhookApp theApp;

/////////////////////////////////////////////////////////////////////////////
// CInstallhookApp initialization

BOOL CInstallhookApp::InitInstance()
{
	// Standard initialization
	HANDLE hMutex = OpenMutex(
      MUTEX_ALL_ACCESS, 0, "92398723.4.0");

    if (!hMutex)
      // Mutex doesn’t exist. This is
      // the first instance so create
      // the mutex.
      hMutex = 
        CreateMutex(0, 0, "92398723.4.0");
    else
      // The mutex exists so this is the
      // the second instance so return.
      return 0;

	CInstallhookDlg dlg;
	m_pMainWnd = &dlg;
	int nResponse = dlg.DoModal();
	if (nResponse == IDOK)
	{
	}
	else if (nResponse == IDCANCEL)
	{
	}

	// Since the dialog has been closed, return FALSE so that we exit the
	//  application, rather than start the application's message pump.
	return FALSE;
}
