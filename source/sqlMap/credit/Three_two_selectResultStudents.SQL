select batchfirm_id,firm.Studentno as studentno,STUDENTS.NAME as name,Creditbatchproject.projectname,firm.credit,
convert(varchar(10),firm.certficatetime,20)as certficatetime,CLASSES.CLASSNAME as classname from
Creditbatchfirm as firm inner join
STUDENTS ON firm.Studentno=STUDENTS.STUDENTNO inner join Creditbatchproject on firm.project_id=Creditbatchproject.batchproject_id
inner join CLASSES ON STUDENTS.CLASSNO=CLASSES.CLASSNO
where firm.project_id=:pid and STUDENTS.STUDENTNO like :studentno
and STUDENTS.CLASSNO like :classno and STUDENTS.NAME like :name and Final_status=1