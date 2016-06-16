select firm.batchfirm_id,firm.credit,convert(varchar(10),firm.certficatetime,20) as certficatetime,firm.Studentno,
STUDENTS.NAME,project.projectname,CLASSES.CLASSNAME from Creditbatchfirm as firm inner join STUDENTS ON
firm.Studentno=STUDENTS.STUDENTNO inner join CLASSES on STUDENTS.CLASSNO=CLASSES.CLASSNO
inner join Creditbatchproject as project on firm.project_id=project.batchproject_id
where firm.project_id=:id AND Final_status=3