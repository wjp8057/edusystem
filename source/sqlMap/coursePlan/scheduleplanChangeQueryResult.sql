select
ScheduleChange.CourseNo  as COURSENO,
COURSES.COURSENAME as  COURSENAME,
schools.name as SCHOOLSNAME,
t4.name as TEACHERNAME4,
'第'+RTRIM(ScheduleChange.Week)+'周，星期'+ScheduleChange.Day+','+TIMESECTIONS.VALUE as TIME,
schedulechange.reason as REASON,
TEACHERS.NAME as TEACHERNAME,
CONVERT(varchar(20),ScheduleChange.SubmitDate,20) as SUBMITDATE,
t2.NAME as TEACHERNAME2,
CONVERT(varchar(20),ScheduleChange.VerifyDate,20) as VERIFYDATE,
t3.Name as TEACHERNAME3,
CONVERT(varchar(20),ScheduleChange.ApproveDate,20) as APPROVEDATE,
ScheduleChange.Enable as ENABLE,
ScheduleChange.RecNo as RECNO
from ScheduleChange inner join COURSES on COURSES.COURSENO=SUBSTRING(ScheduleChange.CourseNo,1,7)
inner join TIMESECTIONS on TIMESECTIONS.NAME=ScheduleChange.Time
inner join SCHOOLS on SCHOOLS.SCHOOL=COURSES.SCHOOL
left outer join USERS on USERS.USERNAME=ScheduleChange.Submit
left outer join TEACHERS on TEACHERS.TEACHERNO=USERS.TEACHERNO
left outer join USERS as u2 on u2.USERNAME=ScheduleChange.Verify
left outer join TEACHERS as t2 on u2.TEACHERNO=t2.TEACHERNO
left outer join USERS as u3 on u3.USERNAME=ScheduleChange.Approve
left outer join TEACHERS as t3 on u3.TEACHERNO=t3.TEACHERNO
inner join teachers as t4 on  t4.teacherno=schedulechange.teacherno
where scheduleChange.Year=:YEAR AND scheduleChange.Term=:TERM and scheduleChange.CourseNo like :COURSENO and ScheduleChange.School=:SCHOOL