package org.intel.misc;
import java.io.IOException;
import java.io.StringReader;
import org.wltea.analyzer.core.IKSegmenter;
import org.wltea.analyzer.core.Lexeme;


public class IKAnalyzerDemo {
	public static void main(String[] args){
		String t = "我们都是中国人";
		System.out.println(t);	
		IKSegmenter ikSeg = new IKSegmenter(new StringReader(t) ,true);
		try {
			Lexeme l = null;
			
			while( (l = ikSeg.next()) != null){
				String[] ss = l.toString().split(":");
	//			System.out.println(ss[1]);
				SpellingToDot trans = new SpellingToDot(ss[1]);
				trans.parseControl();
				trans.myInterface();
			}
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}

	}
}
