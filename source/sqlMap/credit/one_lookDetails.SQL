select STUDENTS.NAME,STUDENTS.STUDENTNO,CLASSES.CLASSNAME,c.description,c.applydate_id,c.projectname,c.projecttype,c.credittype,c.credit,
convert(varchar(10),c.certficatetime,20)as certficatetime,c.description,c.schoolview,c.deanview,convert(varchar(10),c.schoolsubtime,20)as schoolsubtime,
convert(varchar(10),c.deansubtime,20) as deansubtime from STUDENTS LEFT OUTER JOIN CLASSES on STUDENTS.CLASSNO=CLASSES.CLASSNO
LEFT OUTER JOIN CreditSinglefirm AS c on STUDENTS.STUDENTNO=c.Studentno where c.applydate_id=:id