select r.courseno + [group] courseno,coursename,credits,r.studentno,s.name,s2.name schoolname,s2.school
from r32 r,courses c,students s,classes c2,schools s2 
where repeat=1 and r.courseno=c.courseno and r.studentno=s.studentno and s.classno=c2.classno and c2.school=s2.school 
	and (r.[group]<>'CX' and r.[group]<>'CY' and r.[group]<>'CZ') and 
  r.year =:year and term =:term and left(r.courseno,2) like :cSchool and s2.school like :school
order by s2.school