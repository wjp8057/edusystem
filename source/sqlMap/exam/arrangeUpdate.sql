update testcourse set flag=:FLAG1 where lock=0 and  courseno2 like (:COURSENO1)+'%';
update teststudent set flag=:FLAG2 where lock=0 and courseno2 like (:COURSENO2)+'%'