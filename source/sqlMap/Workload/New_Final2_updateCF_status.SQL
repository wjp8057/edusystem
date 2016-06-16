update work set CF_status=1
where exists (
select Totalwork_id from VIEW_CF where VIEW_CF.Totalwork_id=work.Totalwork_id
)