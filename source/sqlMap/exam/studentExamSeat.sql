select scores.studentno as STUDENTNO,students.name as NAME
from scores,students
where courseno+[group]=:COURSENO  and scores.studentno=students.studentno
      and year=:YEAR and term=:TERM and scores.active=1
order by scores.studentno