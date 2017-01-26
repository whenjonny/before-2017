package org.intel.call;

import org.intel.com.Com;

public class PhoneCall {
	Com cc;
	
	public PhoneCall(String phone){
		cc = new Com("");
	}
	
	public void call(String phone){
		cc.sendMessage("atd"+phone+";\r");
	}
	
	public void cancel(){
		cc.sendMessage("ath\r");
	}
	
	public static void main(String[] args){
		new PhoneCall("625543").cancel();
	}
	
}
