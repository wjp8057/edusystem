UPDATE TIMELIST
SET MON=:mon,TUE=:thu,WES=:wes,THU=:thu,FRI=:fri,SAT=:sat,SUN=:sun
WHERE YEAR=:year
AND TERM=:term
AND TYPE=:type
AND WHO=:who
AND PARA=:para