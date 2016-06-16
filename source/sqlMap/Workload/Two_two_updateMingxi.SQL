update work set work=:workload where totalwork_id=(select totalwork_id from totalwork
where rtrim(totalwork.courseno)+RTRIM(totalwork.[group])=:courseno and RTRIM(teachername)=:teachername)