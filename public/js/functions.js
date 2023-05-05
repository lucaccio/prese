 
 if(baseUrl == null) {
    var baseUrl =  '..';
}
 
 
 /**
  * 
  * @param {type} url
  * @param {type} data
  * @param {type} callback
  * @returns {undefined}
  */
 function ajax_call(url, data, callback) 
 {
     $.ajax({
        type: 'post',
        url: baseUrl + url,
        data: data,
        dataType:'json',        
        success: function(data) {
            
            if(callback) {
                if(data.hasOwnProperty('success')) {
                    if(data.success == true ) {
                        callback(data);
                    } else {
                        alert(data.error);
                    }
                } else {
                    callback(data);
                }
            } else {
                return data;
            }
                    
        }, 
        error: function(e,r,t) {
            log_error(e,r,t, this);
        }        
    });
 } 
 
 /**
  * 
  * @param {type} e
  * @param {type} r
  * @param {type} t
  * @param {type} o
  * @returns {undefined}
  */
 function log_error(e,r,t, o) 
 {
    console.log(e);
    console.log(t);
    console.log(r);
    
    if(e.status > 0) {
        switch(e.status) {
            case 401:
                console.log('non loggato: ' + baseUrl + '/auth/login');
                window.location.href = baseUrl + '/auth/login'; 
            break;
               
            case 403:
                alert("Non hai i permessi sufficenti per questa operazione, contatta l'amministrazione.");
            break;
            
            default:
                alert(e.responseText);
            break;
        }
    }
    //loggaErrorToDatabase(e);
 }
 
 
 
 
 /**
  * carico le risposte standard in caso di rifiuto richiesta
  * @returns {undefined}
  */
 function load_standard_response(callback) 
 {
     var url = '/ajax/load-standard-response'; 
     var data = {};
     ajax_call(url, data, callback);
 }
 
 /**
  * 
  * @param {type} data
  * @param {type} callback
  * @returns {undefined}
  */
 function send_refused(data, callback) 
 {
     var url = '/ajax/send-refused'; 
     ajax_call(url, data, callback);
 }
 
 function delete_accepted_request(id, send_mail, callback)
 {
     var url = '/ajax/delete-accepted-request'; 
     var data = {
         id:   id,
         send_mail: send_mail        
     };
     ajax_call(url, data, callback);
 }

 function delete_processing_request(id, uid, callback)
 {
     var url = '/ajax/delete-processing-request'; 
     var data = {
         id:   id,
         uid: uid
     };
     ajax_call(url, data, callback);
 }

/**
 * 
 * @returns {undefined}
 */
 function ask_cancellation_request_accepted(uid, rid, callback)
 {
     var url = '/ajax/ask-cancellation-request-accepted'; 
     var data = {
         rid: rid,
         uid: uid
     };
     ajax_call(url, data, callback);
 }