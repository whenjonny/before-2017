package org.intel.misc;
import java.io.IOException;
import java.io.StringReader;

import org.wltea.analyzer.core.IKSegmenter;
import org.wltea.analyzer.core.Lexeme;


public class MyDemo {
	public static void main(String[] args){
//		String str = "荆a溪b白c石出，天寒红叶稀。 山路元无雨， 空翠湿人衣。";
		String str = "物";

		SpellingToDot test = new SpellingToDot(str);
		test.parseControl();

	}
}                                                                                                                                          