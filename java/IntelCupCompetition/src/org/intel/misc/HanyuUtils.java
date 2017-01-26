package org.intel.misc;
import net.sourceforge.pinyin4j.PinyinHelper;
import net.sourceforge.pinyin4j.format.HanyuPinyinOutputFormat;
import net.sourceforge.pinyin4j.format.exception.BadHanyuPinyinOutputFormatCombination;


public class HanyuUtils {
    private HanyuPinyinOutputFormat format = null;
    private String[] pinyin;
   
    public HanyuUtils()
    {
              format = new HanyuPinyinOutputFormat();
              //������ʾ����
              //format.setToneType(HanyuPinyinToneType.WITHOUT_TONE);
              pinyin = null;
    }

  
    //ת�������ַ�
    public String getCharacterPinYin(char c)
    {
              try
              {
                       pinyin = PinyinHelper.toHanyuPinyinStringArray(c, format);
              }
              catch(BadHanyuPinyinOutputFormatCombination e)
              {
                       e.printStackTrace();
              }
             
              // ���c���Ǻ��֣�toHanyuPinyinStringArray�᷵��null
              if(pinyin == null) return null;
             
              // ֻȡһ������������Ƕ����֣���ȡ��һ������
              return pinyin[0];   
    }
   
    //ת��һ���ַ���
    public String getStringPinYin(String str)
    {
              StringBuilder sb = new StringBuilder();
              String tempPinyin = null;
              for(int i = 0; i < str.length(); ++i)
              {
                       tempPinyin =getCharacterPinYin(str.charAt(i));
                       if(tempPinyin == null)
                       {
                                // ���str.charAt(i)�Ǻ��֣��򱣳�ԭ��
                                sb.append(str.charAt(i));
                       }
                       else
                       {
                                sb.append(tempPinyin);
                       }
              }
              return sb.toString();
    }
}
