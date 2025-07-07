package Oop_Semester_Project;
import java.awt.CardLayout;
import java.awt.Color;
import java.awt.Font;
import java.awt.LayoutManager;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.text.SimpleDateFormat;
import java.time.LocalDate;
import java.time.Period;
import java.time.temporal.ChronoUnit;
import java.util.Calendar;
import javax.swing.JButton;
import javax.swing.JComboBox;
import javax.swing.JFrame;
import javax.swing.JLabel;
import javax.swing.JPanel;
import javax.swing.JTextArea;
import javax.swing.JTextField;

public class Main extends JFrame implements ActionListener {
    JPanel window = new JPanel();
    JLabel startDate = new JLabel();
    JLabel startDateCheck = new JLabel();
    JLabel endDate = new JLabel();
    JLabel endDateCheck = new JLabel();
    JTextArea result = new JTextArea();
    String[] months = new String[]{"January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"};
    JComboBox startMonth;
    JComboBox endMonth;
    JTextField startDay;
    JTextField endDay;
    JTextField startYear;
    JTextField endYear;
    JButton startCheckBtn;
    JButton endCheckBtn;
    JButton calculate;
    Font font1;
    SimpleDateFormat dateOfBirth;
    SimpleDateFormat ageAt;
    SimpleDateFormat thisDay;
    SimpleDateFormat thisMonth;
    SimpleDateFormat thisYear;

    Main() {
        this.startMonth = new JComboBox(this.months);
        this.endMonth = new JComboBox(this.months);
        this.startDay = new JTextField();
        this.endDay = new JTextField();
        this.startYear = new JTextField();
        this.endYear = new JTextField();
        this.startCheckBtn = new JButton();
        this.endCheckBtn = new JButton();
        this.calculate = new JButton();
        this.font1 = new Font("Arial", 1, 14);
        this.dateOfBirth = new SimpleDateFormat("yyyy-MM-dd");
        this.ageAt = new SimpleDateFormat("yyyy-MM-dd");
        this.thisDay = new SimpleDateFormat("d");
        this.thisMonth = new SimpleDateFormat("M");
        this.thisYear = new SimpleDateFormat("y");
        this.setBounds(100, 100, 400, 500);
        this.setDefaultCloseOperation(3);
        this.setLayout(new CardLayout());
        this.add(this.window());
        this.setVisible(true);
    }

    public JPanel window() {
        this.window.setBackground(Color.darkGray);
        this.window.setLayout((LayoutManager)null);
        this.window.setBackground(Color.darkGray);
        this.startDate.setText("DATE OF BIRTH : ");
        this.startDate.setFont(this.font1);
        this.startDate.setForeground(Color.lightGray);
        this.startDate.setBounds(38, 40, 150, 34);
        this.startDateCheck.setText("PENDING....");
        this.startDateCheck.setFont(this.font1);
        this.startDateCheck.setForeground(Color.lightGray);
        this.startDateCheck.setBounds(160, 40, 100, 34);
        this.startMonth.setForeground(Color.darkGray);
        this.startMonth.setBounds(38, 74, 100, 34);
        this.startDay.setForeground(Color.darkGray);
        this.startDay.setBounds(140, 74, 40, 34);
        this.startDay.setHorizontalAlignment(0);
        this.startYear.setForeground(Color.darkGray);
        this.startYear.setBounds(182, 74, 80, 34);
        this.startYear.setHorizontalAlignment(0);
        this.startCheckBtn.setText("Check");
        this.startCheckBtn.setBounds(264, 74, 80, 34);
        this.startCheckBtn.setFocusable(false);
        this.startCheckBtn.addActionListener(this);
        this.endDate.setText("AGE AT THE DATE OF :");
        this.endDate.setFont(this.font1);
        this.endDate.setForeground(Color.lightGray);
        this.endDate.setBounds(38, 110, 160, 34);
        this.endDateCheck.setText("PENDING....");
        this.endDateCheck.setFont(this.font1);
        this.endDateCheck.setForeground(Color.lightGray);
        this.endDateCheck.setBounds(200, 110, 100, 34);
        this.endMonth.setForeground(Color.darkGray);
        this.endMonth.setBounds(38, 144, 100, 34);
        this.endMonth.setSelectedIndex(Integer.parseInt(this.thisMonth.format(Calendar.getInstance().getTime())) - 1);
        this.endDay.setForeground(Color.darkGray);
        this.endDay.setBounds(140, 144, 40, 34);
        this.endDay.setHorizontalAlignment(0);
        this.endDay.setText(this.thisDay.format(Calendar.getInstance().getTime()));
        this.endYear.setForeground(Color.darkGray);
        this.endYear.setBounds(182, 144, 80, 34);
        this.endYear.setHorizontalAlignment(0);
        this.endYear.setText(this.thisYear.format(Calendar.getInstance().getTime()));
        this.endCheckBtn.setText("Check");
        this.endCheckBtn.setBounds(264, 144, 80, 34);
        this.endCheckBtn.setFocusable(false);
        this.endCheckBtn.addActionListener(this);
        this.calculate.setText("Calculate");
        this.calculate.setBounds(38, 190, 306, 32);
        this.calculate.setFocusable(false);
        this.calculate.addActionListener(this);
        this.result.setBounds(38, 234, 306, 200);
        this.result.setForeground(Color.DARK_GRAY);
        this.result.setFont(this.font1);
        this.window.add(this.startDate);
        this.window.add(this.startDateCheck);
        this.window.add(this.startMonth);
        this.window.add(this.startDay);
        this.window.add(this.startYear);
        this.window.add(this.startCheckBtn);
        this.window.add(this.endDate);
        this.window.add(this.endDateCheck);
        this.window.add(this.endMonth);
        this.window.add(this.endDay);
        this.window.add(this.endYear);
        this.window.add(this.endCheckBtn);
        this.window.add(this.calculate);
        this.window.add(this.result);
        return this.window;
    }

    public void checkDate(int y, int m, int d, JLabel label) {
        int daysInJan = 31;
        int daysInFeb = 28;
        if (y % 4 == 0) {
            daysInFeb = 29;
        }

        int daysInMar = 31;
        int daysInApr = 30;
        int daysInMay = 31;
        int daysInJun = 30;
        int daysInJul = 31;
        int daysInAug = 31;
        int daysInSep = 30;
        int daysInOct = 31;
        int daysInNov = 30;
        int daysInDec = 31;
        int[] daysInMonth = new int[]{daysInJan, daysInFeb, daysInMar, daysInApr, daysInMay, daysInJun, daysInJul, daysInAug, daysInSep, daysInOct, daysInNov, daysInDec};
        if (d > daysInMonth[m]) {
            label.setForeground(Color.red);
            label.setText("ERROR");
        } else {
            label.setForeground(Color.green);
            label.setText("READY");
        }

        if (y < 1000) {
            label.setForeground(Color.red);
            label.setText("ERROR");
        } else {
            label.setForeground(Color.green);
            label.setText("READY");
        }

    }

    public void calculate(int d, int D, int m, int M, int y, int Y) {
        if (this.startDateCheck.getText().equals("READY") && this.endDateCheck.getText().equals("READY")) {
            M=M+1;
            m=m+1;
            LocalDate pdate = LocalDate.of(Y, M, D);
            LocalDate now = LocalDate.of(y,m,d);
            Period diff = Period.between(pdate, now);
            int totalMonths = diff.getYears() * 12 + diff.getMonths();
            int leapDays = (int)Math.floor((double)(totalMonths / 48));
            int totalDays = (int) ChronoUnit.DAYS.between(pdate, now);
            int totalHours = totalDays * 24;
            long totalMinutes = (long)totalHours * 60L;
            long totalSeconds = totalMinutes * 60L;
            this.result.setText(String.valueOf("" + diff.getYears() + " years " + diff.getMonths() + " months " + diff.getDays() + " Days\nor " + totalMonths + " months  and " + diff.getDays() + " days\nor " + totalDays + " days\nor " + totalHours + " hours\nor " + totalMinutes + " minutes\nor " + totalSeconds + " seconds\n"));
        }

    }

    public int daysInMonth(int months) {
        int[] daysInMonth = new int[]{31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31};
        int days = 0;

        for(int i = 0; i < months; ++i) {
            days += daysInMonth[i];
        }

        return days;
    }

    public static void main(String[] args) {
        new Main();
    }

    public void actionPerformed(ActionEvent e) {
        int y;
        int m;
        int d;
        if (e.getSource() == this.calculate) {
            y = Integer.parseInt(this.startYear.getText());
            m = this.startMonth.getSelectedIndex();
            d = Integer.parseInt(this.startDay.getText());
            int Y = Integer.parseInt(this.endYear.getText());
            int M = this.endMonth.getSelectedIndex();
            int D = Integer.parseInt(this.endDay.getText());
            this.calculate(d, D, m, M, y, Y);
        }

        if (e.getSource() == this.startCheckBtn) {
            try {
                y = Integer.parseInt(this.startYear.getText());
                m = this.startMonth.getSelectedIndex();
                d = Integer.parseInt(this.startDay.getText());
                this.checkDate(y, m, d, this.startDateCheck);
            } catch (Exception var9) {
            }
        }

        if (e.getSource() == this.endCheckBtn) {
            try {
                y = Integer.parseInt(this.endYear.getText());
                m = this.endMonth.getSelectedIndex();
                d = Integer.parseInt(this.endDay.getText());
                this.checkDate(y, m, d, this.endDateCheck);
            } catch (Exception var8) {
            }
        }

    }
}