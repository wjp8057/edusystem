select  count(*) as Rows
from scheduleChange
inner join courses on courses.courseno=substring(ScheduleChange.courseno,1,7)
inner join schools on schools.school=schedulechange.school
inner join TIMESECTIONS on TIMESECTIONS.NAME=ScheduleChange.Time
inner join teachers on teachers.teacherno=schedulechange.teacherno
where year=:YEAR and term=:TERM and enable=1  and ScheduleChange.school=:SCHOOL
and schedulechange.courseno like :COURSENO AND teachers.name like :TEACHERNAME