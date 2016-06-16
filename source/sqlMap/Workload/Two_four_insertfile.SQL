insert into 工作量存档(Year,Term,TeacherNo,TeacherName,CourseNo,CourseName,Work,Attendent,ClassName)
select :YEAR,:TERM,dd.teacherno,dd.teachername,RTRIM(dd.CourseNo)+RTRIM(dd.[group]),dd.coursename,
case  when isnull(dd.work_CF,123)!=123 THEN dd.work_CF
			when isnull(bb.totalwork_id,123)=123 and dd.code='M1' THEN 0.85*dd.work
else  1*dd.[work]
end  as CF,
dd.Attendent,dd.classname
 from VIEW_CF dd
left join(
select dbo.getOne(b.renshu) as renshu,dbo.getOne(c.totalwork_id) as totalwork_id from (
select teacherno,courseno,max(Attendent) as renshu from VIEW_CF group by courseno,teacherno) as b
left join VIEW_CF c on (b.courseno=c.courseno and b.teacherno=c.teacherno and b.renshu=c.Attendent)
GROUP BY c.courseno,c.teacherno,b.renshu) bb
on (dd.totalwork_id=bb.totalwork_id)
