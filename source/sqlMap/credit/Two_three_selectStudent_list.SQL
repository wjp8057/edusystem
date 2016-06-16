select * from (select row_number() over(order by A.Studentno) as row,A.applydate_id,A.schoolview,A.deanview,convert(varchar(10),
A.createdate,20) as createdate,A.Studentno,B.NAME,A.projectname,A.credit,convert(varchar(10),A.certficatetime,20) as certficatetime,
A.firmno,A.submitter from CreditSinglefirm as A inner join STUDENTS as B on A.Studentno=B.STUDENTNO
WHERE A.year=:year AND A.term=:term and A.Studentno like :studentno AND B.NAME like :studentname and B.CLASSNO like :classno
AND A.projectname like :projectname  AND (A.credittype = :cone or A.credittype=:ctwo) AND A.schoolname like :schoolname AND A.schoolview=1
AND (A.deanview=:d1 or A.deanview=:d2 or A.deanview=:d3)  )as b where b.row between :start and :end
