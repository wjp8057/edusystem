update Creditbatchfirm set Final_status = 1,Final_time=getdate() where batchfirm_id=:id;
update Creditbatchproject set last_time=getdate() where batchproject_id=:pid