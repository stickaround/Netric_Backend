# Data Dir
This is where all data files should live for the backend.

Some of the data is temporary, and some is controlled in version control.

## profile_runs

This is where we put xhprof profile data files for requests. These need to be read
from xhprof UI. This directory should only ever be used in development environments due 
to the amount of data that xhprof generates.