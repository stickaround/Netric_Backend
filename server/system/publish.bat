:: This function builds and publishes the latest working version

:: Build first
:: build.bat
::

:: Get time variables
set hour=%time:~0,2%
if "%hour:~0,1%" == " " set hour=0%hour:~1,1%
echo hour=%hour%
set min=%time:~3,2%
if "%min:~0,1%" == " " set min=0%min:~1,1%
echo min=%min%
set secs=%time:~6,2%
if "%secs:~0,1%" == " " set secs=0%secs:~1,1%
echo secs=%secs%

set year=%date:~-4%
echo year=%year%
set month=%date:~3,2%
if "%month:~0,1%" == " " set month=0%month:~1,1%
echo month=%month%
set day=%date:~0,2%
if "%day:~0,1%" == " " set day=0%day:~1,1%
echo day=%day%


:: Create tag for previous release for rollbacks
svn move -m "Archiving tag" svn://src.aereus.com/var/src/ant/branches/release svn://src.aereus.com/var/src/ant/tags/rel-%year%-%month%-%day%_%hour%-%minute%-%secs%

:: Mege trunk into release
:: cd ..\..\branches
:: svn update release
:: svn merge svn://src.aereus.com/var/src/ant/trunk release

svn copy svn://src.aereus.com/var/src/ant/trunk svn://src.aereus.com/var/src/ant/branches/release  -m "Publishing to Release"
