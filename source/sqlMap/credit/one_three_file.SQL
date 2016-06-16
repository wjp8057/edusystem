insert into AddCredit(studentno,name,reason,credit,date,year,term,类型,类别,certificatedate)
select firm.Studentno,STUDENTS.NAME,firm.projectname,firm.credit,firm.deansubtime,firm.year,firm.term,firm.credittype,FILETYPE.type,firm.certficatetime
from CREDITSINGLEFIRM as firm inner join STUDENTS on firm.Studentno=STUDENTS.STUDENTNO inner join FILETYPE on FILETYPE.id=1
where firm.deanview=1  and year=:year and term=:term