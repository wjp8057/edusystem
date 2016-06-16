select count(*)as ROWS from Creditbatchfirm as firm inner join STUDENTS ON firm.Studentno=STUDENTS.STUDENTNO
inner join CLASSES on STUDENTS.CLASSNO=CLASSES.CLASSNO inner join Creditbatchproject as project on
firm.project_id=project.batchproject_id where firm.project_id=:id AND STUDENTS.STUDENTNO like :studentno AND
 STUDENTS.NAME like :name AND STUDENTS.CLASSNO like :classno AND (firm.Final_status=:Ftwo or firm.Final_status=:Fone) and firm.year=:year and firm.term=:term