package org.intel.misc;
import java.util.regex.*;
import java.util.HashMap;


public class SpellingToDot {
	private String toTranslate;
	private String transResult;
	private HashMap transRecord = new HashMap();
	
	
	public SpellingToDot() 
	{
		initMap();
		transResult = "";
	}
	
	public SpellingToDot(String str)
	{
		initMap();
		toTranslate = str;
		transResult = "";
	}
	
	private void initMap()
	{
		// 声母
		transRecord.put("b", "12");
		transRecord.put("m", "134");
		transRecord.put("d", "145");
		transRecord.put("n", "1345");
		transRecord.put("g", "1245");
		transRecord.put("j", "1245");
		transRecord.put("h", "125");
		transRecord.put("x", "125");
		transRecord.put("ch", "12345");
		transRecord.put("r", "245");
		transRecord.put("c", "14");
		
		transRecord.put("p", "1234");
		transRecord.put("f", "124");
		transRecord.put("t", "2345");
		transRecord.put("l", "123");
		transRecord.put("k", "13");
		transRecord.put("q", "13");
		transRecord.put("zh", "34");
		transRecord.put("sh", "156");
		transRecord.put("z", "1356");
		transRecord.put("s", "234");
		
		// 韵母
		transRecord.put("a", "12");
		transRecord.put("i", "24");
		transRecord.put("v", "346");
		transRecord.put("ai", "246");
		transRecord.put("ei", "2346");
		transRecord.put("ia", "1246");
		transRecord.put("ie", "15");
		transRecord.put("ua", "123456");
		transRecord.put("ui", "2456");
		transRecord.put("ue", "23456");
		transRecord.put("ang", "236");
		transRecord.put("eng", "3456");
		transRecord.put("iang", "1346");
		transRecord.put("ing", "16");
		transRecord.put("uang", "2356");
		transRecord.put("ong", "256");
		transRecord.put("un", "456");
		
		transRecord.put("e", "26");
		transRecord.put("o", "26");
		transRecord.put("u", "136");
		transRecord.put("er", "1235");
		transRecord.put("ao", "235");
		transRecord.put("ou", "12356");
		transRecord.put("iao", "345");
		transRecord.put("iu", "1256");
		transRecord.put("uai", "13456");
		transRecord.put("uo", "135");
		transRecord.put("an", "1236");
		transRecord.put("en", "356");
		transRecord.put("ian", "146");
		transRecord.put("in", "126");
		transRecord.put("uan", "12456");
		transRecord.put("un", "25");
		transRecord.put("van", "12346");
		transRecord.put("iong", "1456");		
		
		// 音调
		transRecord.put("tone1", "1");
		transRecord.put("tone2", "2");
		transRecord.put("tone3", "3");
		transRecord.put("tone4", "23");
		
		// 标点符号
		transRecord.put("。", "5+23");
		transRecord.put(".", "5+23");
		
		transRecord.put("，", "5+0");
		transRecord.put(",", "5+0");
		
		transRecord.put("、", "4+0"); 
		
		transRecord.put(";", "56+0"); 
		transRecord.put("；", "56+0"); 
		
		transRecord.put("？", "5+3"); 
		transRecord.put("?", "5+3"); 
		
		transRecord.put("!", "56+2"); 
		transRecord.put("！", "56+2"); 
		
		transRecord.put("：", "36+0"); 
		transRecord.put(":", "36+0"); 
		
		transRecord.put("“", "45");
		transRecord.put("”", "45");
		transRecord.put("\"", "45");
		
		transRecord.put("'", "45+45");
		transRecord.put("‘", "45+45");
		transRecord.put("’", "45+45");

		transRecord.put("（", "56+3");
		transRecord.put("）", "6+23");
		transRecord.put("(", "56+3");
		transRecord.put(")", "6+23");
		
		transRecord.put("[", "56+23");
		transRecord.put("]", "56+23");
		transRecord.put("【", "56+23");
		transRecord.put("】", "56+23");
		
		transRecord.put("——", "6+36");
		
		transRecord.put("……", "5+5+5");
		
		transRecord.put("《", "5+365");
		transRecord.put("》", "36+25");
		
		transRecord.put("*", "2356+35");
		
		
		// 数字
		transRecord.put("1", "3456+1");
		transRecord.put("2", "3456+12");
		transRecord.put("3", "3456+14");
		transRecord.put("4", "3456+145");
		transRecord.put("5", "3456+15");
		transRecord.put("6", "3456+124");
		transRecord.put("7", "3456+1245");
		transRecord.put("8", "3456+125");
		transRecord.put("9", "3456+24");
		transRecord.put("0", "3456+245");
	}
	
	
	public String myInterface()
	{
		String result = null;
		
		System.out.println(transResult);
		String ss[] = transResult.split(" ");
		for (int i = 0; i < ss.length; i++)
		{
			String ss2[] = ss[i].split("'");
			for (int k = 0; k < ss2.length; k++)
			{
		// process one ss2[k] wait 1 sec
				int value_l, value_h;
				value_l = value_h = 0;
				for (int j = 0; j < ss2[k].length(); j++)
				{
					if (!ss2[k].equals("#"))
					{
						int cur_v = ss2[k].charAt(j)-'0';
						if (cur_v > 3)
						{
							cur_v -= 3;
							value_h += Math.pow(2, cur_v-1);
						}
						else
						{
							value_l += Math.pow(2, cur_v-1);
						}
						
					}
				}
				System.out.print(value_l + " "+ value_h + " ");
			}
		}
		
		return result;
	}
	// 解析私有变量： String toTranslate 以    声母'韵母'音调   的格式输出结果
	public void parseControl()
	{
		for (int i = 0; i < toTranslate.length(); i++)
		{
		
			char c = toTranslate.charAt(i);
			String currentC = java.lang.Character.toString(c);
			if (c != ' ')
			{
				Hanyu hanyu = new Hanyu();
				// Chinese word || punctuation || digit
				if(currentC.matches(("[\\u4E00-\\u9FA5]+")) || currentC.matches(("[^a-zA-Z0-9\u4E00-\u9FA5]")) || currentC.matches(("[0-9]"))) 
					parseEachWord(hanyu.getStringPinYin(currentC));
				}              
				else if (currentC.matches(("[a-zA-Z]"))) // English
				{
			
			//		lookupTransRec(java.lang.Character.toString(c));                  
				}
				else // 非法字符 
				{
					//System.out.println("INVALID CHARACTER!");
				}
		}
	
	}
	
	private void parseEachWord(String c)
	{
		Pattern p = null;
		Matcher m = null;
		
		p = Pattern.compile("([a-zA-Z]+)(1|2|3|4|5)"); // 划分出每一个字的拼音+音调
		m = p.matcher(c);
		while (m.find())
		{
			// m.group(1) 是拼音
			// m.group(2) 是音调
						
			// 针对拼写 划分出声母与韵母
			String s = m.group(1);
			Pattern p2 = Pattern.compile("b|m|d|n|g|j|h|x|ch|r|c|p|f|t|l|k|q|zh|sh|z|s"); 
			Matcher m2 = p2.matcher(s);
		
			String initials = ""; // 声母
			String finals = "";   // 韵母
			if(m2.find() && (m2.group().charAt(0) == s.charAt(0)))
			{
				initials = m2.group();
				finals = s.substring(initials.length(), s.length()); 								
			}
			else if(transRecord.containsKey(s))
				finals = s.substring(0, s.length());
			else
				finals = s.substring(1, s.length());
			// 查map  将声母 韵母 音调  符号对应成编码
			if (!initials.equals("y") && transRecord.containsKey(initials))
			//	System.out.print(transRecord.get(initials).toString()+"'");
				transResult += (transRecord.get(initials).toString()+"'");
			else
			//	System.out.print("#"+"'");
				transResult += ("#"+"'");
			
			if (!(initials.matches("zh|ch|sh|r|z|c|s") && finals.equals("i")))
		//		System.out.print(transRecord.get(finals).toString()+"'");
				transResult += (transRecord.get(finals).toString()+"'");
			else
	//			System.out.print("#"+"'");
				transResult += ("#"+"'");
			if (!m.group(2).equals("5"))
		//		System.out.println(transRecord.get("tone"+m.group(2)));
				transResult += (transRecord.get("tone"+m.group(2)));
			else
		//		System.out.print("#");
				transResult += ("#");
			transResult += (" ");
		}

	}
	
	
	
	
	private void lookupTransRec(String c)
	{
		System.out.println(transRecord.get(c).toString());
	}
	
}
