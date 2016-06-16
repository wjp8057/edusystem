select firm.batchfirm_id,firm.credit,SCHOOLS.NAME AS SNAME,convert(varchar(10),firm.certficatetime,20)
as certficatetime,firm.Studentno,STUDENTS.NAME,project.projectname,CLASSES.CLASSNAME,firm.Final_status from
Creditbatchfirm as firm inner join STUDENTS ON firm.Studentno=STUDENTS.STUDENTNO inner join CLASSES on STUDENTS.CLASSNO=CLASSES.CLASSNO
inner join Creditbatchproject as project on firm.project_id=project.batchproject_id
inner join SCHOOLS on firm.sub_school=SCHOOLS.SCHOOL
where firm.project_id=:id  AND STUDENTS.STUDENTNO like :studentno AND STUDENTS.NAME
 like :name AND STUDENTS.CLASSNO like :classno AND (firm.Final_status=:Ftwo or firm.Final_status=:Fone) and firm.year=:year and firm.term=:term