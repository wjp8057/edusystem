select
ScheduleChange.CourseNo as COURSENO,
COURSES.COURSENAME as COURSENAME,
'第'+RTRIM(ScheduleChange.Week)+'周，星期'+ScheduleChange.Day+','+TIMESECTIONS.VALUE as TIME,
TEACHERS.NAME as SUBMIT,
convert(varchar(20),ScheduleChange.SubmitDate,20) as SUBMITDATE,
schedulechange.recno as RECNO,
ScheduleChange.School as SCHOOL,
ScheduleChange.reason as REASON,
convert(varchar(20),ScheduleChange.VerifyDate,20) as VERIFYDATE,
t2.NAME as VERIFY
from ScheduleChange inner join COURSES on COURSES.COURSENO=SUBSTRING(ScheduleChange.CourseNo,1,7)
inner join USERS on USERS.USERNAME=ScheduleChange.Submit
inner join TEACHERS on TEACHERS.TEACHERNO=USERS.TEACHERNO
inner join TIMESECTIONS on TIMESECTIONS.NAME=ScheduleChange.Time
inner join USERS as u2 on u2.USERNAME=ScheduleChange.Verify
inner join TEACHERS as t2 on u2.TEACHERNO=t2.TEACHERNO
where scheduleChange.Year=:YEAR and  ScheduleChange.Term=:TERM  and ScheduleChange.Approve is Null and scheduleChange.Verify is not null