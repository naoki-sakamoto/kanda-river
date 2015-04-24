var conf = {
	enable_debug_print:		1,					// デバッグ出力有効化（0:無効、1:有効）
	api_endpoint_host:		"127.0.0.1:8081/yell/app/html",	// APIエンドポイント（空白時は現在のURL）
	env:                  0,// 0:ローカル環境 1:テスト環境 2:本番環境
};

var front = {
  get_g_player_id : function() {
    var id = parseInt($("#g_player_id").val());
    if( isNaN(id) ){
      id = 0;
    }
    return id;
  },
//--------------------------------------------------------------
// GETパラメーターを配列で返す
//--------------------------------------------------------------
  getUrlVars:		function() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++) {
      hash = hashes[i].split('=');
      vars.push(hash[0]);
      vars[hash[0]] = hash[1];
    }
    return vars;
  },					// GETパラメーターを配列で返す
  
  loadingStatus : true,          //ローディングステータス
  loading:  function(mode) { //ローディング表示
    if (front.loadingStatus == false) return;
    // ローディング表示
    if( $(".loading").length == 0 ){
      $("body").prepend( $("<span></span>").addClass("loading").html('<img src="img/loader.gif">') );
    }
    if (mode == true) {
      $(".loading").show();
    } else {
      $(".loading").hide();
    }
  },
//--------------------------------------------------------------
//--------------------------------------------------------------
//改行コードをbrタグに変換
//--------------------------------------------------------------
  LF2br : function(txt) {
    txt = txt.replace(/\r\n/g, "\n")
    .replace(/\r/g, "\n")
    .replace(/\n/g, "<br>");
    return txt;
  },
  //--------------------------------------------------------------
  //オブジェクトに値が入力されていることを確認する
  //--------------------------------------------------------------
  isEmpty : function(s) {
    if ( s == null || s == undefined || s == "" ) {
      return true;
    } else {
      return false;
    }
  },
  //--------------------------------------------------------------
  //エラーダイアログ表示
  //--------------------------------------------------------------
  error_dialog : function(json) {
    if (json.status == 0) return true;
    if (front.isEmpty(json.title) == true && front.isEmpty(json.body) == true) {
      front.show_dialog(json.title,json.body);
    } else {
      front.show_dialog("error","原因不明のエラーが発生しました。");
    }
  },
  //--------------------------------------------------------------
  //ダイアログ表示
  //--------------------------------------------------------------
  show_dialog : function(title_text, body_text) {
    if( $("#dialog").length == 0 ){
      $("body").append( $('<div id="dialog" style="display:none"></div>') );
    }
    $("#dialog").html(body_text);
    $("#dialog").dialog({
      modal: true,
      title: title_text,
      height: "auto",
      buttons: {
        OK: function(){
          $("#dialog").dialog("close");
        }
      }
    });
  },
  //--------------------------------------------------------------
  //オブジェクトをJSON形式に変換
  //--------------------------------------------------------------
  string2json : function (obj) {
    var t = typeof (obj);
    if (t != "object" || obj === null) {
      // simple data type
      if (t == "string") {
        obj = '"' + obj + '"';
      }
      return String(obj);
    }
    else {
      var n=null, v, json = [];
      var arr = (obj && $.isArray(obj));

      for (n in obj) {
        v = obj[n];
        t = typeof(v);
        if (obj.hasOwnProperty(n)) {
          if (t == "string") {
            v = '"' + v + '"';
          }
          else if (t == "object" && v !== null) {
            v = front.string2json(v);
          }
          json.push((arr ? "" : '"' + n + '":') + String(v));
        }
      }
      return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
    }
  },
};

//--------------------------------------------------------------
// API呼び出し
//--------------------------------------------------------------
function call_api( option ){
	//
	var url = "";
	//
	if( conf.api_endpoint_host == "" ){
		str = location.pathname;
		pathname = str.substr(str.lastIndexOf("/")+1);//ファイル名だけを取得する
		//url = location.protocol + "//" + conf.api_endpoint_host + "/" + pathname + location.search;
		if (pathname.split(".")[0] == "") {
		  url = "index.php";
		} else {
		  url = pathname.split(".")[0] + ".php";
		}
	} else {
    str = location.pathname;
    pathname = str.substr(str.lastIndexOf("/")+1);//ファイル名だけを取得する
    url = location.protocol + "//" + conf.api_endpoint_host + "/" + pathname + location.search;
  }
	//
	if( option.data != null ){
		if( option.api != "" ){
			option.data.api = option.api;
		}
		if( option.async == null ){
			option.async = true;
		}
		//d(option.async);
		// token追加
		if( option.data.token == null ){
			//option.data.token = front.get_token();
		}
		//loading
    if( option.loading == null ){
      option.loading = true;
    }
	}
	if (option.loading == true) {
	  front.loadingStatus = true;
	} else {
	  front.loadingStatus = false;
	}
	d("url #",url);
	d("request",option.data);
	//
	$.ajax({
		//
		url: url,
		dataType: "text",
		data: option.data,
		type: "POST",
		async: option.async,
		success: function(data){
		  try {
  	    data = utf8to16(zip_inflate(base64decode(data)));
  	    data =$.parseJSON(data);
      	d("response",data);
  			if( option.callback ){
  				option.callback(data);
  			}
      } catch (e) {
        d("e.message #",e.message);
        //front.errorMessage();
      }
		},
		error: function(data){
			d("ajax error");
	    if( option.error ){
	     option.error(data);
	    }
		},
		// リクエストを送る前に行う処理
		beforeSend: function(xhr){
      //xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
			front.loading(true);
		},
		complete: function(){
      front.loading(false);
		},
	});
}


//--------------------------------------------------------------
// 保存完了時の共通処理
//--------------------------------------------------------------
function save_done( json ){
	json = json[0];
	//
	if( json && json.status == 0 ){
		show_dialog( "", language_switch("saved.","保存しました") );
	}else{
		var msg = (json&&json.msg) ? json.msg : "";
		show_dialog( "error", msg );
	}
}
//--------------------------------------------------------------
// ダイアログ表示
//--------------------------------------------------------------
function show_dialog( title_text, body_text ){
	if( $("#dialog").length == 0 ){
		$("body").append( $('<div id="dialog" style="display:none"></div>') );
	}
	$("#dialog").html(body_text);
	$("#dialog").dialog({
		modal: true,
		title: title_text,
		height: "auto",
		buttons: {
			OK: function(){
				$("#dialog").dialog("close");
			}
		}
	});
}
//--------------------------------------------------------------
//ダイアログ表示
//--------------------------------------------------------------
function confirm_dialog( json ){
	json = json[0];
	if( json && json.status == 0 ){

	}
	show_dialog(json.title,json.msg);
}

//--------------------------------------------------------------
// デバッグ出力
//--------------------------------------------------------------
function d(s1,s2){
	if( conf.enable_debug_print ){
    if (s2 === undefined) console.log( s1 );
		else console.log( s1,s2 );
	}
}


/**
 *  submitを実行する
 *
 * @since     1.0
 * @param     string    $url   submitを実行するURL
 * @access    public
 * @return    void
 */
function act( $url ) {
  this.document.proj.action = $url;
  this.document.proj.target = "_self";
  this.document.proj.submit();
  return false;
}

  /**
   *
   *
   * @since     1.0
   * @param     string    $url   submitを実行するURL
   *            string    $btn_event   イベント名
   * @access    public
   * @return    void
   */
function act_event( $url, $btn_event ) {
  this.document.proj.btn_event.value = $btn_event;
  act($url);

  return false;
}