//This JavaScript is native to Sakomoto

$(document).ready(function() { repod.ajax_post.init(); });
try { repod; } catch(a) { repod = {}; }
repod.ajax_post = {
	init: function() {
                if(!repod.thread_updater){
                        console.log("Ajax posting requires thread_updater");
                        return;
                }
                
		this.config = {
			enabled: repod.suite_settings && repod_jsuite_getCookie("ajax_posting_enabled") ? repod_jsuite_getCookie("ajax_posting_enabled") === "true" : true
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Miscellaneous',read:this.config.enabled,variable:'ajax_posting_enabled',label:'Ajax posting',hover:''}});
		this.update();
	},
	update: function() {
		if (repod.ajax_post.config.enabled) {
                        $("#postform").submit(function(e){
                                e.preventDefault();
                                $("#postsubmit").attr("disabled","disabled");
                                formdata=new FormData(this);
                                formdata.append("json_response", "true");
                                
                                $(document).trigger("ajax_before_post", formdata);
                                
                                $.ajax({
                                        url:this.action,
                                        type:"POST",
                                        data:formdata,
                                        cache:false,
                                        processData: false,
                                        contentType: false,
                                        success:function(post_response){
                                                $("#postsubmit").removeAttr("disabled");
                                                if(post_response.error){
                                                        alert(post_response.error);
                                                        return;
                                                }
                                                alert("Post submitted.");
                                                repod.thread_updater.load_thread_url();
                                                $(document).trigger("ajax_after_post", post_response);
                                        },
                                        error:function(){
                                                alert("Something went wrong.");
                                                $("#postsubmit").removeAttr("disabled");
                                        }
                                },"json");
                        });
		}
	}
}
