select count(*) as ROWS from Creditbatchfirm as firm inner join STUDENTS ON firm.Studentno=STUDENTS.STUDENTNO
inner join Creditbatchproject on firm.project_id=Creditbatchproject.batchproject_id
inner join CLASSES ON STUDENTS.CLASSNO=CLASSES.CLASSNO where firm.project_id=:pid and Final_status=:Final_status
and firm.year=:year and firm.term=:term