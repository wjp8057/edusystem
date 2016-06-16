select  distinct dbo.GROUP_CONCAT(RTRIM(classes.classname),',') AS CLASSNAME
from courseplan inner join classes on classes.classno=courseplan.classno
where courseplan.courseno+courseplan.[group]=:COURSENO
AND COURSEPLAN.YEAR=:YEAR AND COURSEPLAN.TERM=:TERM