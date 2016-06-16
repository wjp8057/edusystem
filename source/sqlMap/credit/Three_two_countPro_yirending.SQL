select count(*) AS ROWS from Creditbatchproject where audit_deptid=:schoolno and p_status=1
AND (credittype=:cone or credittype=:ctwo) and projectname like :projectname