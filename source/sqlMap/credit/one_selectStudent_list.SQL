select * from (select row_number() over(order by createdate) as row,
A.applydate_id,A.Studentno,B.NAME,A.projectname,A.credit,convert(varchar(10),A.certficatetime,20) as
certficatetime,A.firmno,A.schoolview,A.deanview from CreditSinglefirm as A inner join STUDENTS as B on A.Studentno=B.STUDENTNO
WHERE A.year=:year AND A.term=:term and A.Studentno like :studentno AND B.NAME like :studentname AND A.projectname like :projectname
AND A.projecttype like :projecttype AND (A.credittype = :cone or A.credittype=:ctwo)
AND(A.schoolview=:vone or A.schoolview=:vtwo or A.schoolview=:vthree or A.schoolview=:vfour)
AND schoolname=:schoolname )as b
where b.row between :start and :end