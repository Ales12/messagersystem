# messagersystem
Mit diesem System können eure Charaktere SMS/Nachrichten schreiben. Es ist im Stil von WhatsApp aufgebaut. 

## benötigte Plugins
x-threads

## präfix für x-threads
messager_

Dieses muss bei dem Forum angegeben werden, in welchem der Messager laufen soll.

## neue DB-Spalten im threads
**präfix_threads**
- messager_partner
- messager_groupchattitle
- messager_grouppic
- messager_kind

**präfix_posts**
- message_date
- message_time

## neue Templates
- messager_editmessage 	
- messager_editmessage_firstpost 	
- messager_forumdisplay_icon 	
- messager_forumdisplay_thread
- messager_forumdisplay_thread_modbit
- messager_forumdisplay_threadlist
- messager_globalchats 	
- messager_messagedate 	
- messager_misc 	
- messager_misc_chats 	
- messager_newchat_facts 	
- messager_postbit_classic 	
- messager_replaychat 	
- messager_showthread 	
- messager_showthread_icons_both 	
- messager_showthread_icons_single 	
- messager_showthread_infos 	
- messager_threadreview

## CSS 
messager.css
```
.messager_icons{
			display: flex;
			align-items: center;
		}
		
		.messager_icons i{
			font-size:16px;
			padding: 0 10px;
		}
		
		.messager_chat{
			display: flex;
			align-items: center;
			width: 80%;
			margin: 5px auto;
			gap: 5px 10px;
		}
		
		.messager_chat > div{
			margin: 3px;	
		}
		
		.messager_pic{
					height:50px;
			width:50px;
			border-radius: 100%;
			margin-right: 10px;
		}
		
		.messager_pic img{
					height:50px;
			width:50px;
			border-radius: 100%;
		}
		
		.messager_lastmessagebox{
			padding: 2px;	
			width: 100%;
		}
		
		.messager_messagename{
			font-weight: bold;
			font-size: 15px;
		}
		
		.messager_messagedate{
			float: right;
			font-size:9px;
		}
		
		
.messager_lastmessage{
	font-size: 11px;	
}

/* Showthread */

.showthread_head{
	display: flex;
	gap: 0 30px;
	align-items: center;
	padding:10px 20px;
}

.showthread_icon{
	display: flex;
	justify-content: center;
	align-items: center;
}

.showthread_icon_single{
	height: 60px;
	width: 60px;
	border-radius: 100%;
}

.showthread_icon_single img{
	height: 60px;
	width: 60px;
	border-radius: 100%;
}

.showthread_name{
	font-size: 20px;
	font-weight: bold;
}

.showthread_icons{
	display: flex;
	justify-content: center;
	align-items: center;
}


.showthread_icon{
	height: 60px;
	width: 60px;
	border-radius: 100%;
	position: relative;
	left: 10px;
}

.showthread_icon img{
	height: 60px;
	width: 60px;
	border-radius: 100%;
}

/* Postbit */
.messager {
	padding: 4px 20px;
}

.message_own {
	display: flex;
	align-items: center;
	flex-direction:row-reverse;
	padding: 20px 30px 20px 20px;
		flex-wrap: wrap;
}

.message_other {
	display: flex;
	align-items: center;
		padding: 20px;
	flex-wrap: wrap;
	
}


.message_account_pic{
	width: 120px;
	height: 120px;
	margin: 20px 10px;
}


.message_account_pic img{
	width: 100px;
	height: 100px;
	border-radius: 100%;
}

.message_own .message_post_body{
	width: 80%;
	border-radius: 20px 0 20px 20px;
	background: #ddd;
	padding: 10px 20px;
  box-sizing: border-box;

}


.message_other .message_post_body{
	width: 80%;
	border-radius: 0 20px 20px 20px ;
	background: #ddd;
	padding: 10px 20px;
  box-sizing: border-box;

}


.message_own .post_head{
	text-align: right;
	font-size: 10px;
	width: 100%;
	display: flex;
	gap: 5px 10px;
	flex-direction: row-reverse;
  margin-bottom: 10px;
}

.message_other .post_head{
	font-size: 10px;
		width: 100%;
	display: flex;
		gap: 5px 10px;
	margin-bottom: 10px;
}

.message_own .message_post_body::before{
	content: "";
	width: 0;
  height: 0;
border-top: 20px solid #ddd;
  border-right: 20px solid transparent;
  position: relative;
top: -10px;
  float: right;
  left: 39px;
}

.message_other .message_post_body::before{
	content: "";
	width: 0;
  height: 0;
border-top: 20px solid #ddd;
  border-left: 20px solid transparent;
  position: relative;
  top: 9px;
  left: -39px;
}

.message_own .message_bottom{
	width: 100%;	
	text-align: right;
	padding: 2px 10px 2px 0;
}
.message_other .message_bottom{
	width: 100%;	
	padding: 2px 0 2px 10px;
}


.message_profile{
	font-size: 14px;
	padding: 2px 5px;
}

.messager_datetime{
	font-size: 8px;
	text-align: right;
}

.message{
	padding: 2px;	
}
/*Forumdisplay*/
.messager_forumdisplay{
	display: flex;
	align-items: center;
	gap: 5px 20px;
}

.messager_forumdisplay_icons{
	width: 120px;
	height: 120px;
	display: flex;
	justify-content: center;
	align-items: center;
}

.messager_icon{
	height: 60px;
	width: 60px;
	border-radius: 100%;
	position: relative;
	left: 10px;
}

.messager_icon img{
	height: 60px;
	width: 60px;
	border-radius: 100%;
}

.messager_groupicon img{
		height: 80px;
	width:80px;
	border-radius: 100%;
}


.messager_forumdisplay_messager{
	width: 100%;
	padding: 10px;
	box-sizing: border-box;
}

.messager_forumdisplay_chat{
	font-size: 20px;
	font-weight: bold;
}

.messager_forumdisplay_lastpost{
	font-size: 10px;
	padding-left: 10px;
	text-transform: uppercase;
}
```
