update work set work=(
select
case
  when code='M1' then
        case
						when CAST(Attendent as int)=0
							then
								case
                  when rtrim(xklx)='M' or rtrim(xklx)='T'
											then W_number*CAST(mzks as decimal(10,1))*1/cishu.cs
									else
                       W_number*CAST(mzks as decimal(10,1))*0.9/cishu.cs
								end
					  when (rtrim(xklx)='M' or rtrim(xklx)='T') and  cast((1+(CAST(Attendent as decimal(10,3))-CAST(Standard as decimal(10,3)))/CAST(Attendent as decimal(10,3)))as decimal(10,3))<1
                  then W_number*CAST(mzks as decimal(10,3))*1/cishu.cs
						when cast((1+(CAST(Attendent as decimal(10,3))-CAST(Standard as decimal(10,3)))/CAST(Attendent as decimal(10,3)))as decimal(10,2))<0.9
							then W_number*CAST(mzks as decimal(10,3))*0.9/cishu.cs
            when cast((1+(CAST(Attendent as decimal(10,3))-CAST(Standard as decimal(10,3)))/CAST(Attendent as decimal(10,3)))as decimal(10,2))>1.6
                 then W_number*CAST(mzks as decimal(10,3))*1.6/cishu.cs
            else
                  cast((1+(CAST(Attendent as decimal(10,3))-CAST(Standard as decimal(10,3)))/CAST(Attendent as decimal(10,3))) as decimal(10,3))*W_number*CAST(mzks as decimal(10,1))/cishu.cs
        end
  when code='M21' or code='M22'
         then W_number*CAST(mzks as decimal(10,2))*0.5/cishu.cs

  when code='M23'
       then CAST(Attendent as int)*W_number*0.15/cishu.cs
  when code='M5'
       then CAST(Attendent as int)*1.2/cishu.cs
  when code='M3'
         then
             case
                when substring(classno,3,1)=4
                  then CAST(Attendent as int)*4/cishu.cs
                else
                   CAST(Attendent as int)*2/cishu.cs
             end

  when code='M8'
          then
              CAST(Attendent as decimal(10,2))/CAST(Standard as decimal(10,2))*W_number*CAST(mzks as decimal(10,1))/cishu.cs
    when code='M9'
          then
              CAST(Attendent as decimal(10,2))/CAST(Standard as decimal(10,2))*W_number*CAST(mzks as decimal(10,1))*2/cishu.cs
end as work from totalwork as t
inner join
(select count(1) as cs,Courseno,[group] from totalwork group by courseno,[group]) as cishu on cishu.Courseno=t.Courseno and cishu.[group]=t.[group]
where work.Totalwork_id=t.Totalwork_id
)
where work.Totalwork_id=:TID

