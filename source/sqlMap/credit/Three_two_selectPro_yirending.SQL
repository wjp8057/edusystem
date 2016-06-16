select * from
(select row_number() over(order by createdate)as row,
batchproject_id,projectname,credit,credittype,convert(varchar(10),certficatetime,20)as certficatetime,
audit_deptid from Creditbatchproject where audit_deptid=:schoolno and projectstatus=1 AND
(credittype=:cone or credittype=:ctwo) and projectname like :projectname and p_status=1)as b where b.row between :start and :end