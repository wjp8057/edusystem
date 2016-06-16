select totalwork_id from totalwork
where rtrim(totalwork.courseno)+RTRIM(totalwork.[group])=:courseno and RTRIM(teachername)=:teachername and year=:year and term=:term