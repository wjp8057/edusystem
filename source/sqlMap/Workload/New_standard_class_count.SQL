select  count(*) as ROWS from(select row_number() over(order by Courseno) as row from totalwork where Courseno like :courseno and Coursename like :coursename and kclx like :kclx and xklx
like :xklx
and school like :school and (code=='M1'or code='M8') and status=0) as b