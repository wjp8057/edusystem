insert into evaluation_ddpj(teacherno,courseno,year,term,score,ratio,date,supervisor) 
values(:teacherno,:courseno,:year,:term,:score,:ratio,getdate(),:supervisor)