UPDATE SCHEDULEPLAN SET HALFLOCK=0
WHERE YEAR=:YEAR AND TERM=:TERM AND COURSENO+[GROUP] LIKE :COURSENO
