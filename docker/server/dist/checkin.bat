if "%1" == "update" GOTO UPDATE
if "%1" == "add" GOTO ADD

:: Default checkin
:: cvs -q ci -m ""
svn ci -m "" 
GOTO END

:: Update local repo
:UPDATE
:: cvs -q update -d
svn update
GOTO END

:: Add file to cvs
:ADD
:: cvs add %2
svn add %2
GOTO END

:END

