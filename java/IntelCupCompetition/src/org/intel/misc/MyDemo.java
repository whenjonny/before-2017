package org.intel.misc;
import java.io.IOException;
import java.io.StringReader;

import org.wltea.analyzer.core.IKSegmenter;
import org.wltea.analyzer.core.Lexeme;


public class MyDemo {
	public static void main(String[] args){
//		String str = "��aϪb��cʯ�����캮��Ҷϡ�� ɽ·Ԫ���꣬ �մ�ʪ���¡�";
		String str = "��";

		SpellingToDot test = new SpellingToDot(str);
		test.parseControl();

	}
}                                                                                                                                          