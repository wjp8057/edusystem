select distinct s1.name schoolname,r.courseno+r.[group] courseno,coursename,credits,r.studentno,s.name,s2.name sName
	from r32 r,courses c,students s,schools s1,schools s2,classes c2,courseplan c3 
	where repeat=1 and r.courseno=c.courseno and  
	r.studentno=s.studentno and c.school=s1.school and s.classno=c2.classno and c2.school = s2.school and 
	r.year =:year and r.term =:term and 
	c3.courseno+c3.[group]=r.courseno+r.[group] and 
	c3.classno = '222222' and s1.school like :cSchool and s2.school like :school 
	order by schoolname,courseno