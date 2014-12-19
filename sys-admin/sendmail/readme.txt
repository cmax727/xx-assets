----------
in windows, you need to copy and extract sendmail.zip

and set the sendmail_path to the full path of sendmail.exe
see php.ini file

# sendmail_path = "W:\wamp\bin\sendmail\sendmail.exe -t"
# smtp_port = 25
# SMTP = localhost
# sendmail_from = you@yourdomain
   // you can change sendmail_from  using ini_set('sendmail_from', 'youmail@example.com') 
---------- 
in linux
you need to install sendmail
using 
$ apt-get install sendmail
or
$ yum install sendmail

( to remove it, #yum erase sendmail)

and configure sm
