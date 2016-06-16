select
ScheduleChange.CourseNo as COURSENO,
COURSES.COURSENAME as COURSENAME,
t2.name as TEACHERNAME,
'第'+RTRIM(ScheduleChange.Week)+'周，星期'+ScheduleChange.Day+','+TIMESECTIONS.VALUE as TIME,
TEACHERS.NAME as SUBMIT,
convert(varchar(20),ScheduleChange.SubmitDate,20) as SUBMITDATE,
schedulechange.recno as RECNO,
ScheduleChange.School as SCHOOL,
ScheduleChange.reason as REASON,
ScheduleChange.RecNo as RECNO
from ScheduleChange inner join COURSES on COURSES.COURSENO=SUBSTRING(ScheduleChange.CourseNo,1,7)
inner join USERS on USERS.USERNAME=ScheduleChange.Submit
inner join TEACHERS on TEACHERS.TEACHERNO=USERS.TEACHERNO
inner join TIMESECTIONS on TIMESECTIONS.NAME=ScheduleChange.Time
inner join teachers as t2 on t2.teacherno=schedulechange.teacherno
where schedulechange.Year=:YEAR and schedulechange.Term=:TERM and schedulechange.School like :SCHOOL and ScheduleChange.Verify is Null
