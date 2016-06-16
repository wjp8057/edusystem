select A.Studentno,B.NAME,A.projectname,A.credit,convert(varchar(10),A.certficatetime,20) as certficatetime,A.firmno,
convert(varchar(10),A.createdate,20) as createdate from CREDITSINGLEFIRM as A inner join STUDENTS as B on A.Studentno=B.STUDENTNO
WHERE A.year=:YEAR AND A.term=:TERM and A.Studentno like :STUDENTNO AND B.NAME like :NAME AND A.projectname like :PROJECTNAME AND A.projecttype like :PROJECTTYPE
AND (A.credittype = :CONE or A.credittype=:CTWO) AND (A.schoolview=1 or A.schoolview=2 or A.schoolview=1)AND schoolname=:SCHOOLNAME