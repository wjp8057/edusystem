insert into AddCredit(studentno,name,reason,credit,date,year,term,类型,报送,类别,certificatedate)
select firm.studentno,STUDENTS.NAME,firm.projectname,firm.credit,firm.addtime,firm.year,
firm.term,firm.credittype,SCHOOLS.NAME,FILETYPE.type,firm.certficatetime
from CREDITCOUNT as firm inner join STUDENTS on firm.Studentno=STUDENTS.STUDENTNO
inner JOIN FILETYPE on FILETYPE.id=2
inner join SCHOOLS ON SCHOOLS.SCHOOL=firm.subschool
where firm.status=1 and year=:year and term=:term