package org.intel.jtts;


public class Trans {
	
	public Trans() {}
	
	public void tanslate(String sen) {
		JTTS jtts= Engine.getTTS();
		jtts.setLanguage("zh");
		jtts.setRate(150);
		jtts.setVolume(150);
		// 璁惧畾鏈楄鏂囨�?
		jtts.speak(sen);
	}
}
