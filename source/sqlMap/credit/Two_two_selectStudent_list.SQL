select * from (select row_number()
over(order by A.Studentno) as row,A.applydate_id,A.schoolview,A.deanview,convert(varchar(10),A.createdate,20) as createdate,
A.Studentno,B.NAME,A.projectname,A.credit,convert(varchar(10),A.certficatetime,20) as certficatetime,A.firmno,A.submitter
 from CreditSinglefirm as A inner join STUDENTS as B on A.Studentno=B.STUDENTNO WHERE A.year=:year AND A.term=:term
 and A.Studentno like :studentno AND B.NAME like :studentname and B.CLASSNO like :classno AND A.projectname like :projectname
 AND (A.credittype = :cone or A.credittype=:ctwo) AND (A.schoolview=:v1 or A.schoolview=:v2 or A.schoolview=:v3)
 AND schoolname like :schoolname  )as b where b.row between :start and :end
