select courses.coursename as km,:cone as kh,convert(varchar(10),getdate(),20) as dysj
from scheduleplan inner join courses on courses.courseno=substring(scheduleplan.courseno,1,7)
where scheduleplan.courseno+scheduleplan.[group]=:ctwo and scheduleplan.year =:year and scheduleplan.term=:term