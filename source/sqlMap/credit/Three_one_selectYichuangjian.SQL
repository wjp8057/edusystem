select * from(select row_number() over(order by batchproject_id) as row,batchproject_id,projectname,projectstatus,credittype,credit,
convert(varchar(10),certficatetime,20)
as certficatetime,schools_ids,audit_deptid from Creditbatchproject where(credittype=:cone or credittype=:ctwo) and
projectname like :projectname AND projectstatus=1
) as b where b.row between :start and :end