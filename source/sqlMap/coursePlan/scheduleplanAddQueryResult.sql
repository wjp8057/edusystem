select
schedulechange.recno AS RECNO,
ScheduleChange.courseno AS COURSENO,
courses.coursename as COURSENAME,
schools.name as SCHOOLNAME,
'第'+RTRIM(ScheduleChange.Week)+'周，星期'+ScheduleChange.Day+','+TIMESECTIONS.VALUE as TIME,
addtion as ADDTION,
teachers.name as TEACHERNAME,
ScheduleChange.week,day,
schedulechange.School as SCHOOL
from ScheduleChange
inner join courses on courses.courseno=substring(ScheduleChange.courseno,1,7)
inner join schools on schools.school=schedulechange.school
inner join TIMESECTIONS on TIMESECTIONS.NAME=ScheduleChange.Time
inner join teachers on teachers.teacherno=schedulechange.teacherno
where year=:YEAR and term=:TERM and enable=1  and ScheduleChange.school=:SCHOOL and schedulechange.courseno like :COURSENO
AND teachers.name like :TEACHERNAME
order by ScheduleChange.week,day