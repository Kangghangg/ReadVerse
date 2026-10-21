<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Category;
use App\Models\Ebook;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    private const TARGET = 60;
    private const MIN_BYTES = 40960;
    private const MAX_BYTES = 83886080;
    private const MAX_ARCHIVE_BYTES = 47185920;

    private const HINTS = [
        'tech' => ['công nghệ', 'thông tin', 'lập trình', 'máy tính', 'tin học', 'phần mềm', 'kỹ thuật', 'dữ liệu', 'trí tuệ', 'technology', 'computer', 'programming'],
        'sci' => ['khoa học', 'toán', 'vật lý', 'hóa', 'sinh học', 'tự nhiên', 'science', 'math', 'physics'],
        'lit' => ['văn học', 'tiểu thuyết', 'truyện', 'thơ', 'văn', 'novel', 'fiction', 'literature'],
        'phil' => ['triết', 'tư tưởng', 'tôn giáo', 'tâm linh', 'philosophy'],
        'self' => ['kỹ năng', 'phát triển', 'tâm lý', 'tự lực', 'đời sống', 'self', 'skill'],
        'biz' => ['kinh tế', 'kinh doanh', 'tài chính', 'quản trị', 'marketing', 'đầu tư', 'economics', 'business', 'finance'],
        'hist' => ['lịch sử', 'chính trị', 'xã hội', 'địa lý', 'history', 'politic'],
    ];

    private const NEIGHBORS = [
        'tech' => ['sci'],
        'sci' => ['tech', 'phil'],
        'lit' => ['phil', 'hist', 'self'],
        'phil' => ['lit', 'self', 'hist', 'sci'],
        'self' => ['phil', 'biz', 'hist'],
        'biz' => ['self', 'hist', 'phil'],
        'hist' => ['phil', 'lit', 'biz'],
    ];

    private const CATALOG = <<<'TXT'
tech|Eloquent JavaScript|Marijn Haverbeke|https://eloquentjavascript.net/Eloquent_JavaScript.pdf|Giáo trình JavaScript hiện đại, đi từ cú pháp nền tảng đến lập trình hàm, DOM, Node.js và các dự án thực hành.|Lập trình viên người Hà Lan, tác giả trình soạn thảo CodeMirror.
tech|Think Python|Allen B. Downey|https://greenteapress.com/thinkpython2/thinkpython2.pdf|Nhập môn Python cho người mới, tập trung vào tư duy lập trình và cách giải quyết vấn đề.|Giáo sư khoa học máy tính tại Olin College, tác giả loạt sách Think.
tech|Think Java|Allen B. Downey|https://greenteapress.com/thinkjava7/thinkjava7.pdf https://greenteapress.com/thinkjava7/thinkjava.pdf https://greenteapress.com/thinkjava6/thinkjava.pdf|Giới thiệu lập trình hướng đối tượng với Java qua các ví dụ ngắn gọn và bài tập có lời giải.|
tech|Think Data Structures|Allen B. Downey|https://greenteapress.com/thinkdast/thinkdast.pdf|Cấu trúc dữ liệu và thuật toán bằng Java, xây dựng một bộ máy tìm kiếm đơn giản từ các thành phần cơ bản.|
tech|Think OS|Allen B. Downey|https://greenteapress.com/thinkos/thinkos.pdf|Hệ điều hành qua góc nhìn lập trình C: bộ nhớ, tiến trình, luồng và hệ thống tệp.|
tech|Think Stats|Allen B. Downey|https://greenteapress.com/thinkstats2/thinkstats2.pdf|Xác suất và thống kê cho lập trình viên, thực hành bằng Python trên dữ liệu thực tế.|
tech|Think Bayes|Allen B. Downey|https://greenteapress.com/thinkbayes/thinkbayes.pdf|Thống kê Bayes tiếp cận bằng mã nguồn Python thay cho các công thức toán học nặng nề.|
tech|Algorithms|Jeff Erickson|https://jeffe.cs.illinois.edu/teaching/algorithms/book/Algorithms-JeffE.pdf|Giáo trình thuật toán đầy đủ: đệ quy, quy hoạch động, đồ thị, luồng mạng và NP-đầy đủ.|Giáo sư khoa học máy tính tại Đại học Illinois Urbana-Champaign.
tech|Open Data Structures|Pat Morin|https://opendatastructures.org/ods-java.pdf|Trình bày chặt chẽ các cấu trúc dữ liệu cơ bản cùng phân tích độ phức tạp và cài đặt bằng Java.|Giáo sư tại Đại học Carleton, Canada.
tech|Structure and Interpretation of Computer Programs|Harold Abelson|https://web.mit.edu/6.001/6.037/sicp.pdf|Giáo trình kinh điển của MIT về trừu tượng hóa, đệ quy và xây dựng bộ thông dịch bằng Scheme.|Giáo sư tại MIT, đồng tác giả cùng Gerald Jay Sussman.
tech|An Introduction to Statistical Learning|Gareth James|https://hastie.su.domains/ISLR2/ISLRv2_corrected_June_2023.pdf https://www.statlearning.com/s/ISLRv2_corrected_June_2023.pdf|Nhập môn học máy thống kê với các ứng dụng thực hành bằng ngôn ngữ R.|Giáo sư thống kê tại Đại học Nam California.
tech|The Elements of Statistical Learning|Trevor Hastie|https://hastie.su.domains/ElemStatLearn/printings/ESLII_print12_toc.pdf|Tài liệu tham khảo nền tảng về khai phá dữ liệu, suy luận và dự đoán thống kê.|Giáo sư thống kê tại Đại học Stanford.
tech|Mining of Massive Datasets|Jure Leskovec|http://infolab.stanford.edu/~ullman/mmds/book0n.pdf|Các kỹ thuật khai phá dữ liệu quy mô lớn: MapReduce, tập mục thường xuyên, phân cụm và đồ thị web.|Giáo sư khoa học máy tính tại Đại học Stanford.
tech|Pattern Recognition and Machine Learning|Christopher M. Bishop|https://www.microsoft.com/en-us/research/uploads/prod/2006/01/Bishop-Pattern-Recognition-and-Machine-Learning-2006.pdf|Giáo trình học máy theo hướng xác suất, bao quát mạng nơ-ron, mô hình đồ thị và suy luận Bayes.|Nhà nghiên cứu học máy tại Microsoft Research Cambridge.
tech|Information Theory, Inference, and Learning Algorithms|David J. C. MacKay|https://www.inference.org.uk/itprnn/book.pdf|Kết hợp lý thuyết thông tin, suy luận và học máy trong một giáo trình giàu trực giác.|Nhà vật lý và lý thuyết thông tin người Anh, giáo sư Đại học Cambridge.
tech|The Linux Command Line|William Shotts|https://sourceforge.net/projects/linuxcommand/files/TLCL/19.01/TLCL-19.01.pdf/download|Hướng dẫn dòng lệnh Linux từ thao tác cơ bản đến viết shell script hoàn chỉnh.|Chuyên gia Linux, tác giả trang LinuxCommand.org.
tech|Beej's Guide to Network Programming|Brian Hall|https://beej.us/guide/bgnet/pdf/bgnet_usl_c_1.pdf|Hướng dẫn lập trình socket bằng C, từ TCP, UDP đến các kỹ thuật máy chủ đa kết nối.|Lập trình viên nổi tiếng với bộ hướng dẫn Beej's Guide.
tech|Beej's Guide to C Programming|Brian Hall|https://beej.us/guide/bgc/pdf/bgc_usl_c_2.pdf https://beej.us/guide/bgc/pdf/bgc_usl_c_1.pdf|Giáo trình ngôn ngữ C thực dụng, giải thích con trỏ, bộ nhớ và các điểm dễ sai.|
tech|Reinforcement Learning: An Introduction|Richard S. Sutton|http://incompleteideas.net/book/RLbook2020.pdf|Giáo trình nền tảng về học tăng cường: quá trình quyết định Markov, Q-learning và policy gradient.|Giáo sư Đại học Alberta, một trong những người đặt nền móng cho học tăng cường.
tech|Understanding Machine Learning|Shai Shalev-Shwartz|https://www.cs.huji.ac.il/~shais/UnderstandingMachineLearning/understanding-machine-learning-theory-algorithms.pdf|Cơ sở lý thuyết của học máy: PAC, độ phức tạp mẫu, SVM và các thuật toán tối ưu.|Giáo sư khoa học máy tính tại Đại học Hebrew Jerusalem.
tech|Foundations of Data Science|Avrim Blum|https://www.cs.cornell.edu/jeh/book.pdf|Nền tảng toán học của khoa học dữ liệu: không gian nhiều chiều, SVD, đồ thị ngẫu nhiên và học máy.|Giáo sư khoa học máy tính tại Toyota Technological Institute at Chicago.
tech|Bayesian Data Analysis|Andrew Gelman|http://www.stat.columbia.edu/~gelman/book/BDA3.pdf|Giáo trình phân tích dữ liệu Bayes với mô hình phân cấp và suy luận bằng mô phỏng.|Giáo sư thống kê và khoa học chính trị tại Đại học Columbia.
tech|Deep Learning with PyTorch|Eli Stevens|https://pytorch.org/assets/deep-learning/Deep-Learning-with-PyTorch.pdf|Xây dựng và huấn luyện mạng học sâu với PyTorch qua dự án xử lý ảnh y khoa.|Kỹ sư phần mềm và học máy, đồng tác giả cùng Luca Antiga và Thomas Viehmann.
tech|Python for Everybody|Charles Severance|https://do1.dr-chuck.com/pythonlearn/EN_us/pythonlearn.pdf|Nhập môn lập trình bằng Python, từ biến, vòng lặp đến cơ sở dữ liệu và web.|Giáo sư tại Trường Thông tin, Đại học Michigan.
tech|An Introduction to Computer Networks|Peter Dordal|http://intronetworks.cs.luc.edu/current2/ComputerNetworks.pdf http://intronetworks.cs.luc.edu/current/ComputerNetworks.pdf|Giáo trình mạng máy tính từ tầng liên kết đến tầng ứng dụng, có phần thực hành với Wireshark.|Giáo sư khoa học máy tính tại Đại học Loyola Chicago.
tech|Advanced Linux Programming|Mark Mitchell|https://mentorembedded.github.io/advancedlinuxprogramming/alp-folder/advanced-linux-programming.pdf|Lập trình hệ thống trên Linux: tiến trình, luồng, IPC, thiết bị và lời gọi hệ thống.|Kỹ sư phần mềm, đồng sáng lập CodeSourcery.
tech|A Graduate Course in Applied Cryptography|Dan Boneh|https://crypto.stanford.edu/~dabo/cryptobook/BonehShoup_0_6.pdf https://crypto.stanford.edu/~dabo/cryptobook/BonehShoup_0_5.pdf|Giáo trình mật mã ứng dụng: mã hóa đối xứng, khóa công khai, chữ ký số và giao thức.|Giáo sư khoa học máy tính tại Đại học Stanford.
sci|Introduction to Probability|Charles M. Grinstead|https://math.dartmouth.edu/~prob/prob/prob.pdf|Giáo trình xác suất rời rạc và liên tục với nhiều ví dụ mô phỏng và bài tập.|Giáo sư toán học tại Swarthmore College.
sci|Linear Algebra|Jim Hefferon|https://hefferon.net/linearalgebra/book.pdf https://joshua.smcvt.edu/linearalgebra/book.pdf|Đại số tuyến tính từ hệ phương trình đến trị riêng, có đầy đủ lời giải bài tập.|Giáo sư toán tại Saint Michael's College, Vermont.
sci|Convex Optimization|Stephen Boyd|https://web.stanford.edu/~boyd/cvxbook/bv_cvxbook.pdf|Giáo trình tối ưu lồi kinh điển: lý thuyết, thuật toán và ứng dụng trong kỹ thuật.|Giáo sư kỹ thuật điện tại Đại học Stanford.
sci|Relativity: The Special and General Theory|Albert Einstein||Chính Einstein trình bày thuyết tương đối hẹp và rộng bằng ngôn ngữ dành cho độc giả phổ thông.|Nhà vật lý lý thuyết người Đức, giải Nobel Vật lý năm 1921.
sci|Calculus Made Easy|Silvanus P. Thompson||Cuốn nhập môn vi tích phân nổi tiếng với giọng văn gần gũi, xóa tan nỗi sợ toán cao cấp.|Nhà vật lý và kỹ sư điện người Anh.
sci|The Chemical History of a Candle|Michael Faraday||Loạt bài giảng nổi tiếng dùng ngọn nến để giải thích hóa học và vật lý.|Nhà khoa học người Anh, có đóng góp nền tảng cho điện từ học.
sci|Flatland|Edwin A. Abbott||Tiểu thuyết toán học về thế giới hai chiều, mở ra cách nghĩ về không gian nhiều chiều.|Nhà giáo và nhà thần học người Anh.
sci|On the Origin of Species|Charles Darwin||Công trình đặt nền móng cho thuyết tiến hóa bằng chọn lọc tự nhiên.|Nhà tự nhiên học người Anh, cha đẻ của thuyết tiến hóa.
sci|Science and Hypothesis|Henri Poincaré||Suy ngẫm về vai trò của giả thuyết, hình học và quy ước trong khoa học.|Nhà toán học và triết gia khoa học người Pháp.
lit|Pride and Prejudice|Jane Austen||Câu chuyện tình yêu và định kiến giữa Elizabeth Bennet và Mr Darcy trong xã hội Anh đầu thế kỷ XIX.|Nhà văn người Anh, tác giả của Emma và Sense and Sensibility.
lit|Frankenstein|Mary Shelley||Nhà khoa học trẻ tạo ra một sinh vật sống và phải đối diện với hậu quả khôn lường.|Nhà văn người Anh, tác giả của nhiều tiểu thuyết gothic.
lit|Dracula|Bram Stoker||Tiểu thuyết kinh dị kinh điển, kể qua nhật ký và thư từ về bá tước ma cà rồng xứ Transylvania.|Nhà văn người Ireland.
lit|The Picture of Dorian Gray|Oscar Wilde||Chàng trai giữ mãi vẻ đẹp tuổi trẻ trong khi bức chân dung dần phơi bày sự suy đồi của anh.|Nhà văn và nhà viết kịch người Ireland.
lit|Alice's Adventures in Wonderland|Lewis Carroll||Cuộc phiêu lưu kỳ ảo của cô bé Alice sau khi rơi xuống hang thỏ.|Nhà toán học và nhà văn người Anh, tên thật Charles Dodgson.
lit|The Adventures of Sherlock Holmes|Arthur Conan Doyle||Mười hai vụ án của thám tử Sherlock Holmes qua lời kể của bác sĩ Watson.|Nhà văn và bác sĩ người Scotland.
lit|Treasure Island|Robert Louis Stevenson||Cuộc tìm kho báu của cậu bé Jim Hawkins cùng thủy thủ đoàn lẫn băng hải tặc.|Nhà văn người Scotland.
lit|Great Expectations|Charles Dickens||Hành trình trưởng thành của cậu bé mồ côi Pip với những kỳ vọng và bài học về lòng tự trọng.|Nhà văn người Anh, đại diện tiêu biểu của văn học thời Victoria.
lit|A Tale of Two Cities|Charles Dickens||Bi kịch gia đình và tình yêu giữa London và Paris trong thời kỳ Cách mạng Pháp.|
lit|Wuthering Heights|Emily Brontë||Mối tình đầy ám ảnh giữa Heathcliff và Catherine trên những cánh đồng hoang Yorkshire.|Nhà văn và nhà thơ người Anh.
lit|Jane Eyre|Charlotte Brontë||Cô gia sư mồ côi tự khẳng định bản thân và đi tìm tình yêu xứng đáng.|Nhà văn người Anh, chị của Emily và Anne Brontë.
lit|The Metamorphosis|Franz Kafka||Một buổi sáng, Gregor Samsa thức dậy và thấy mình hóa thành một con bọ khổng lồ.|Nhà văn người Séc viết bằng tiếng Đức.
lit|The Time Machine|H. G. Wells||Nhà phát minh du hành đến năm 802701 và chứng kiến tương lai của nhân loại.|Nhà văn người Anh, cha đẻ của nhiều ý tưởng khoa học viễn tưởng hiện đại.
lit|The War of the Worlds|H. G. Wells||Người sao Hỏa xâm lược nước Anh trong tác phẩm khoa học viễn tưởng tiên phong.|
lit|The Call of the Wild|Jack London||Chú chó Buck bị bắt cóc đến vùng băng giá Alaska và dần thức tỉnh bản năng hoang dã.|Nhà văn người Mỹ.
lit|Strange Case of Dr Jekyll and Mr Hyde|Robert Louis Stevenson||Bác sĩ Jekyll chế tạo một loại thuốc tách bản thân thành hai nhân cách đối lập.|
lit|The Scarlet Letter|Nathaniel Hawthorne||Hester Prynne mang chữ A đỏ trên ngực và đối mặt với sự phán xét của cộng đồng thanh giáo.|Nhà văn người Mỹ.
lit|Heart of Darkness|Joseph Conrad||Chuyến ngược dòng sông Congo của Marlow và sự thật về Kurtz, về chủ nghĩa thực dân.|Nhà văn gốc Ba Lan viết bằng tiếng Anh.
lit|The Importance of Being Earnest|Oscar Wilde||Vở hài kịch châm biếm giới thượng lưu xoay quanh hai chàng trai dùng tên giả Ernest.|
lit|Peter Pan|J. M. Barrie||Câu chuyện về cậu bé không bao giờ lớn và chuyến bay đến xứ Neverland.|Nhà văn và nhà viết kịch người Scotland.
lit|Around the World in Eighty Days|Jules Verne||Phileas Fogg đặt cược sẽ vòng quanh thế giới trong tám mươi ngày.|Nhà văn người Pháp, tiên phong của thể loại khoa học viễn tưởng.
lit|The Adventures of Tom Sawyer|Mark Twain||Những trò nghịch ngợm và phiêu lưu của cậu bé Tom bên bờ sông Mississippi.|Nhà văn người Mỹ, tên thật Samuel Clemens.
lit|Candide|Voltaire||Chàng trai ngây thơ đi qua chuỗi tai ương và dần nhận ra chủ nghĩa lạc quan mù quáng.|Triết gia và nhà văn Khai sáng người Pháp.
phil|Meditations|Marcus Aurelius||Những ghi chép triết học cá nhân của hoàng đế La Mã về lý trí, bổn phận và sự điềm tĩnh.|Hoàng đế La Mã và triết gia phái Khắc kỷ.
phil|The Art of War|Sun Tzu||Luận thuyết quân sự cổ điển Trung Hoa về chiến lược, cũng là sách tham khảo trong quản trị.|Nhà quân sự và chiến lược gia Trung Hoa cổ đại.
phil|The Prince|Niccolò Machiavelli||Cẩm nang về quyền lực và nghệ thuật cai trị, bàn về chính trị thực dụng.|Nhà ngoại giao và triết gia chính trị người Ý thời Phục hưng.
phil|Beyond Good and Evil|Friedrich Nietzsche||Phê phán đạo đức truyền thống và các giáo điều triết học, mở đường cho tư tưởng hiện đại.|Triết gia người Đức, ảnh hưởng lớn đến triết học hiện sinh.
phil|Utopia|Thomas More||Mô tả hòn đảo lý tưởng để phê phán xã hội châu Âu đương thời.|Luật sư, triết gia và chính khách người Anh.
phil|Walden|Henry David Thoreau||Hai năm sống giản dị bên hồ Walden cùng những suy ngẫm về tự lập và thiên nhiên.|Nhà văn và triết gia người Mỹ.
self|As a Man Thinketh|James Allen||Tiểu luận ngắn về sức mạnh của suy nghĩ đối với tính cách và hoàn cảnh sống.|Nhà văn người Anh viết về triết lý tự lực.
self|The Elements of Style|William Strunk Jr.||Cẩm nang nguyên tắc viết tiếng Anh rõ ràng, ngắn gọn và chính xác.|Giáo sư tiếng Anh tại Đại học Cornell.
self|The Art of Public Speaking|Dale Carnegie||Hướng dẫn nói trước công chúng: chuẩn bị, trình bày và chinh phục người nghe.|Tác giả và diễn giả người Mỹ về kỹ năng giao tiếp.
self|The Science of Getting Rich|Wallace D. Wattles||Quan điểm về tư duy tích cực và hành động có hệ thống để tạo dựng tài sản.|Tác giả người Mỹ viết về phát triển bản thân.
self|The Autobiography of Benjamin Franklin|Benjamin Franklin||Hồi ký về hành trình từ cậu thợ in nghèo đến nhà khoa học, chính khách và người lập quốc Mỹ.|Chính khách, nhà khoa học và nhà phát minh người Mỹ.
biz|Principles of Scientific Management|Frederick Winslow Taylor||Nền tảng của quản trị khoa học: phân công lao động, đo lường và nâng cao năng suất.|Kỹ sư cơ khí người Mỹ, cha đẻ của quản trị khoa học.
biz|The Theory of the Leisure Class|Thorstein Veblen||Phân tích tiêu dùng phô trương và cấu trúc giai tầng trong xã hội công nghiệp.|Nhà kinh tế và xã hội học người Mỹ.
biz|The Richest Man in Babylon|George S. Clason||Những dụ ngôn về tiết kiệm, đầu tư và kỷ luật tài chính đặt trong thành Babylon cổ.|Nhà văn và doanh nhân người Mỹ.
biz|Acres of Diamonds|Russell H. Conwell||Bài diễn thuyết nổi tiếng: cơ hội làm giàu thường nằm ngay trong tầm tay.|Mục sư, nhà giáo dục và diễn giả người Mỹ.
hist|The Communist Manifesto|Karl Marx||Văn kiện chính trị có ảnh hưởng lớn, trình bày quan điểm về đấu tranh giai cấp và chủ nghĩa tư bản.|Triết gia và nhà kinh tế người Đức.
hist|Common Sense|Thomas Paine||Cuốn sách mỏng cổ vũ độc lập của các thuộc địa Mỹ khỏi chính quyền Anh.|Nhà tư tưởng và hoạt động chính trị người Anh gốc Mỹ.
hist|Up from Slavery|Booker T. Washington||Tự truyện của người sinh ra trong chế độ nô lệ, sau trở thành nhà giáo dục hàng đầu nước Mỹ.|Nhà giáo dục và hoạt động xã hội người Mỹ gốc Phi.
hist|The Souls of Black Folk|W. E. B. Du Bois||Tuyển tập tiểu luận về chủng tộc, giáo dục và đời sống người Mỹ gốc Phi sau nội chiến.|Nhà xã hội học, sử gia và nhà hoạt động dân quyền người Mỹ.
TXT;

    private array $failed = [];
    private array $authorCache = [];
    private array $authorTemplate = [];
    private array $template = [];
    private array $cols = [];
    private array $map = [];

    public function run(): void
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $out = $this->command;

        $this->cols = Schema::getColumnListing('ebooks');
        $this->map = [
            'file' => $this->firstCol(['file_path', 'pdf_path', 'file', 'pdf_file']),
            'cover' => collect($this->cols)->first(fn ($c) => str_contains($c, 'cover') && ! str_ends_with($c, '_url'))
                ?? collect($this->cols)->first(fn ($c) => str_contains($c, 'cover'))
                ?? $this->firstCol(['thumbnail', 'image', 'image_path']),
            'desc' => $this->firstCol(['description', 'summary', 'synopsis']),
            'pages' => $this->firstCol(['pages', 'page_count', 'total_pages', 'num_pages']),
            'views' => $this->firstCol(['views', 'view_count', 'views_count']),
        ];

        if (! $this->map['file'] || ! $this->map['cover']) {
            $out->error('Không nhận diện được cột file hoặc bìa của bảng ebooks. Các cột hiện có: '.implode(', ', $this->cols));
            return;
        }

        $query = Ebook::query();
        if (in_array('status', $this->cols, true)) {
            $query->orderByRaw("case when status in ('approved','published') then 0 else 1 end");
        }
        $first = $query->orderBy('id')->first();
        if (! $first) {
            $out->error('Bảng ebooks đang trống, cần ít nhất một sách mẫu để nhân bản cấu trúc.');
            return;
        }
        $this->template = Arr::except($first->getAttributes(), ['id', 'created_at', 'updated_at', 'deleted_at']);
        $firstAuthor = Author::query()->orderBy('id')->first();
        $this->authorTemplate = $firstAuthor ? Arr::except($firstAuthor->getAttributes(), ['id', 'created_at', 'updated_at', 'deleted_at']) : ['name' => ''];

        $this->removeTestBooks();

        $categories = Category::query()->orderBy('id')->get();
        if ($categories->isEmpty()) {
            $out->error('Chưa có danh mục nào trong bảng categories.');
            return;
        }

        $genresOf = [];
        foreach ($categories as $category) {
            $genresOf[$category->id] = $this->genresOf((string) $category->name);
        }

        $need = max(self::TARGET - DB::table('ebooks')->count(), 0);
        $counts = DB::table('ebooks')->select('category_id', DB::raw('count(*) as c'))->groupBy('category_id')->pluck('c', 'category_id')->all();
        $quota = [];
        foreach ($categories as $category) {
            $counts[$category->id] = $counts[$category->id] ?? 0;
            $quota[$category->id] = 0;
        }
        for ($i = 0; $i < $need; $i++) {
            $target = null;
            foreach ($categories as $category) {
                if ($target === null || $counts[$category->id] < $counts[$target]) {
                    $target = $category->id;
                }
            }
            $counts[$target]++;
            $quota[$target]++;
        }

        $out->info("Cần thêm {$need} sách vào ".$categories->count().' danh mục.');

        $existing = DB::table('ebooks')->pluck('slug')->all();
        $pool = $this->catalog();
        mt_srand(20261014);
        shuffle($pool);
        $pool = array_values(array_filter($pool, fn ($i) => ! in_array(Str::slug($i['title']), $existing, true)));

        $used = [];
        $remaining = $quota;
        $done = 0;

        while (array_sum($remaining) > 0) {
            $moved = false;
            foreach ($categories as $category) {
                if (($remaining[$category->id] ?? 0) < 1) {
                    continue;
                }
                while (true) {
                    $idx = $this->pick($pool, $used, $genresOf[$category->id]);
                    if ($idx === null) {
                        $remaining[$category->id] = 0;
                        break;
                    }
                    $used[$idx] = true;
                    $item = $pool[$idx];
                    $out->line('  ... '.Str::limit($item['title'], 60).' -> '.$category->name);
                    if ($this->importBook($item, $category)) {
                        $done++;
                        $remaining[$category->id]--;
                        $moved = true;
                        $out->info(sprintf('  [%02d/%d] OK  %s', $done, $need, $item['title']));
                        break;
                    }
                    $out->warn('        bỏ qua, thử cuốn khác');
                }
            }
            if (! $moved) {
                break;
            }
        }

        $out->newLine();
        $out->info('Hoàn tất. Tổng số sách hiện có: '.DB::table('ebooks')->count());
        $rows = $categories->map(fn ($c) => [
            $c->name,
            implode(',', $genresOf[$c->id]) ?: '-',
            DB::table('ebooks')->where('category_id', $c->id)->count(),
        ])->all();
        $out->table(['Danh mục', 'Nhóm sách', 'Số sách'], $rows);

        if ($this->failed) {
            $out->warn('Không tải hoặc không lưu được ('.count($this->failed).'): '.implode(' | ', $this->failed));
            $out->line('Chạy lại lệnh để thử bổ sung phần còn thiếu.');
        }
    }

    private function removeTestBooks(): void
    {
        if (getenv('REPLACE_TEST_BOOKS') === '0') {
            return;
        }

        $fileCol = $this->map['file'];
        $ids = Ebook::query()->get()
            ->filter(fn ($e) => preg_match('/(sach1|sample)\.pdf$/i', (string) $e->{$fileCol}))
            ->pluck('id')->all();

        if (! $ids) {
            return;
        }

        try {
            foreach (Schema::getTables() as $table) {
                $name = $table['name'];
                if ($name !== 'ebooks' && Schema::hasColumn($name, 'ebook_id')) {
                    DB::table($name)->whereIn('ebook_id', $ids)->delete();
                }
            }
            DB::table('ebooks')->whereIn('id', $ids)->delete();
            $this->command->info('Đã gỡ '.count($ids).' sách thử nghiệm dùng sach1.pdf.');
        } catch (\Throwable $e) {
            $this->command->warn('Không gỡ được sách thử nghiệm: '.Str::limit($e->getMessage(), 160));
        }
    }

    private function catalog(): array
    {
        $rows = [];
        $bios = [];
        foreach (preg_split('/\R/', trim(self::CATALOG)) as $line) {
            $p = array_map('trim', explode('|', $line));
            if (count($p) < 6) {
                continue;
            }
            [$genre, $title, $author, $urls, $desc, $bio] = $p;
            if ($bio !== '' && ! isset($bios[$author])) {
                $bios[$author] = $bio;
            }
            $rows[] = [
                'genre' => $genre,
                'title' => $title,
                'author' => $author,
                'urls' => preg_split('/\s+/', $urls, -1, PREG_SPLIT_NO_EMPTY),
                'desc' => $desc,
            ];
        }

        return array_map(function ($row) use ($bios) {
            $row['bio'] = $bios[$row['author']] ?? ('Tác giả của cuốn '.$row['title'].'.');
            return $row;
        }, $rows);
    }

    private function genresOf(string $name): array
    {
        $n = Str::lower($name);
        $genres = [];
        foreach (self::HINTS as $genre => $words) {
            foreach ($words as $w) {
                if (str_contains($n, $w)) {
                    $genres[] = $genre;
                    break;
                }
            }
        }

        return $genres;
    }

    private function pick(array $pool, array $used, array $genres): ?int
    {
        $order = [];
        foreach ($genres as $g) {
            $order[] = $g;
        }
        foreach ($genres as $g) {
            foreach (self::NEIGHBORS[$g] ?? [] as $n) {
                $order[] = $n;
            }
        }
        foreach (array_values(array_unique($order)) as $g) {
            foreach ($pool as $i => $item) {
                if (! isset($used[$i]) && $item['genre'] === $g) {
                    return $i;
                }
            }
        }
        foreach ($pool as $i => $item) {
            if (! isset($used[$i])) {
                return $i;
            }
        }

        return null;
    }

    private function importBook(array $item, Category $category): bool
    {
        $slug = Str::slug($item['title']);
        if (Ebook::query()->where('slug', $slug)->exists()) {
            return false;
        }

        $disk = Storage::disk('public');
        $disk->makeDirectory('ebooks/files');
        $disk->makeDirectory('ebooks/covers');
        $rel = "ebooks/files/{$slug}.pdf";
        $abs = $disk->path($rel);

        if (! $this->isPdf($abs)) {
            $ok = $item['urls'] && $this->fetchPdf($item['urls'], $abs);
            if (! $ok) {
                $found = $this->discoverArchive($item);
                $ok = $found && $this->fetchPdf([$found], $abs);
            }
            if (! $ok) {
                $this->failed[] = $item['title'];
                return false;
            }
        }

        try {
            $coverRel = $this->makeCover($item, $slug, $abs);
            $authorId = $this->authorId($item['author'], $item['bio']);

            $data = $this->template;
            $data['title'] = $item['title'];
            $data['slug'] = $slug;
            $data['category_id'] = $category->id;
            if (array_key_exists('author_id', $data) || in_array('author_id', $this->cols, true)) {
                $data['author_id'] = $authorId;
            }
            $data[$this->map['file']] = $rel;
            $data[$this->map['cover']] = str_ends_with($this->map['cover'], '_url') ? '/storage/'.$coverRel : $coverRel;
            if ($this->map['desc']) {
                $data[$this->map['desc']] = $item['desc'];
            }
            if ($this->map['pages']) {
                $data[$this->map['pages']] = $this->pageCount($abs);
            }
            if ($this->map['views']) {
                $data[$this->map['views']] = random_int(120, 9800);
            }
            if (in_array('price', $this->cols, true)) {
                $data['price'] = Arr::random([0, 0, 0, 49000, 79000, 99000, 129000, 159000, 199000]);
            }
            if (array_key_exists('isbn', $data)) {
                $data['isbn'] = null;
            }
            if (isset($data['status']) && ! in_array(strtolower((string) $data['status']), ['approved', 'published'], true)) {
                $data['status'] = 'approved';
            }
            if (in_array('created_at', $this->cols, true)) {
                $data['created_at'] = now()->subDays(random_int(1, 150))->subMinutes(random_int(0, 1440));
            }

            $ebook = new Ebook();
            $ebook->forceFill($data)->save();

            $url = (string) ($ebook->cover_url ?? '');
            if (! str_contains($url, '/storage/'.$coverRel)) {
                $ebook->forceFill([$this->map['cover'] => '/storage/'.$coverRel])->save();
            }

            return true;
        } catch (\Throwable $e) {
            $this->failed[] = $item['title'].' ('.Str::limit($e->getMessage(), 140).')';
            return false;
        }
    }

    private function firstCol(array $names): ?string
    {
        foreach ($names as $n) {
            if (in_array($n, $this->cols, true)) {
                return $n;
            }
        }

        return null;
    }

    private function ua(string $url): string
    {
        return str_contains($url, 'sourceforge.net') ? 'curl/8.4.0' : 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) ReadVerseCatalog/1.0';
    }

    private function isPdf(string $path): bool
    {
        if (! is_file($path)) {
            return false;
        }
        $size = filesize($path);
        if ($size < self::MIN_BYTES || $size > self::MAX_BYTES) {
            return false;
        }
        $h = fopen($path, 'rb');
        $head = fread($h, 1024);
        fclose($h);

        return str_contains((string) $head, '%PDF-');
    }

    private function fetchPdf(array $urls, string $path): bool
    {
        foreach ($urls as $url) {
            try {
                $res = Http::timeout(300)->connectTimeout(20)->withUserAgent($this->ua($url))
                    ->withOptions(['sink' => $path])->get($url);
                if ($res->successful() && $this->isPdf($path)) {
                    return true;
                }
            } catch (\Throwable $e) {
            }
            @unlink($path);
        }

        return false;
    }

    private function lastName(string $author): string
    {
        $clean = trim(preg_replace('/\b(jr|sr|ii|iii)\.?$/i', '', trim($author)));
        $parts = preg_split('/\s+/', $clean);

        return (string) end($parts);
    }

    private function norm(string $s): string
    {
        return preg_replace('/[^a-z0-9]+/', '', Str::lower(Str::ascii($s)));
    }

    private function discoverArchive(array $item): ?string
    {
        try {
            $core = trim(preg_replace('/^(the|a|an)\s+/i', '', str_replace('"', '', $item['title'])));
            $last = $this->lastName($item['author']);
            $queries = [
                'title:("'.$core.'") AND creator:('.$last.') AND mediatype:(texts) AND language:(eng) AND NOT access-restricted-item:true',
                'title:("'.$core.'") AND mediatype:(texts) AND language:(eng) AND NOT access-restricted-item:true',
            ];

            foreach ($queries as $q) {
                $url = 'https://archive.org/advancedsearch.php?q='.rawurlencode($q)
                    .'&fl%5B%5D=identifier&fl%5B%5D=title&rows=8&sort%5B%5D=downloads+desc&output=json';
                $res = Http::timeout(40)->withUserAgent($this->ua($url))->get($url);
                if (! $res->successful()) {
                    continue;
                }
                foreach ($res->json('response.docs', []) as $doc) {
                    $id = $doc['identifier'] ?? null;
                    $title = is_array($doc['title'] ?? null) ? ($doc['title'][0] ?? '') : ($doc['title'] ?? '');
                    if (! $id || ! str_contains($this->norm((string) $title), $this->norm($core))) {
                        continue;
                    }
                    $meta = Http::timeout(40)->withUserAgent($this->ua($url))->get('https://archive.org/metadata/'.$id);
                    if (! $meta->successful()) {
                        continue;
                    }
                    $pdfs = collect($meta->json('files', []))
                        ->filter(fn ($f) => str_ends_with(strtolower($f['name'] ?? ''), '.pdf')
                            && (int) ($f['size'] ?? 0) >= 153600
                            && (int) ($f['size'] ?? 0) <= self::MAX_ARCHIVE_BYTES)
                        ->sortBy(fn ($f) => (int) $f['size']);
                    if ($pdfs->isEmpty()) {
                        continue;
                    }
                    $name = $pdfs->first()['name'];

                    return 'https://archive.org/download/'.$id.'/'.implode('/', array_map('rawurlencode', explode('/', $name)));
                }
            }
        } catch (\Throwable $e) {
        }

        return null;
    }

    private function makeCover(array $item, string $slug, string $pdfAbs): string
    {
        $dir = Storage::disk('public')->path('ebooks/covers');
        foreach (['jpg', 'png', 'svg'] as $ext) {
            $file = "{$dir}/{$slug}.{$ext}";
            if (is_file($file) && filesize($file) > 1500) {
                return "ebooks/covers/{$slug}.{$ext}";
            }
        }

        $bytes = $this->openLibraryCover($item);
        if ($bytes) {
            file_put_contents("{$dir}/{$slug}.jpg", $bytes);
            return "ebooks/covers/{$slug}.jpg";
        }

        if ($this->pdfThumbnail($pdfAbs, "{$dir}/{$slug}.png")) {
            return "ebooks/covers/{$slug}.png";
        }

        file_put_contents("{$dir}/{$slug}.svg", $this->svgCover($item, $slug));

        return "ebooks/covers/{$slug}.svg";
    }

    private function openLibraryCover(array $item): ?string
    {
        try {
            $res = Http::timeout(30)->withUserAgent($this->ua('https://openlibrary.org'))
                ->get('https://openlibrary.org/search.json', [
                    'title' => $item['title'],
                    'author' => $item['author'],
                    'limit' => 6,
                    'fields' => 'title,author_name,cover_i',
                ]);
            if (! $res->successful()) {
                return null;
            }
            $wanted = $this->norm($item['title']);
            $last = $this->norm($this->lastName($item['author']));

            foreach ($res->json('docs', []) as $doc) {
                if (empty($doc['cover_i'])) {
                    continue;
                }
                $t = $this->norm((string) ($doc['title'] ?? ''));
                $authors = $this->norm(implode(' ', $doc['author_name'] ?? []));
                if ($t === '' || $last === '' || ! str_contains($authors, $last)) {
                    continue;
                }
                similar_text($t, $wanted, $pct);
                if (! (str_contains($t, $wanted) || str_contains($wanted, $t) || $pct > 80)) {
                    continue;
                }
                $img = Http::timeout(40)->withUserAgent($this->ua('https://covers.openlibrary.org'))
                    ->get('https://covers.openlibrary.org/b/id/'.$doc['cover_i'].'-L.jpg?default=false');
                $body = $img->successful() ? $img->body() : '';
                if (strlen($body) > 8000 && str_starts_with($body, "\xFF\xD8")) {
                    return $body;
                }
            }
        } catch (\Throwable $e) {
        }

        return null;
    }

    private function pdfThumbnail(string $pdf, string $target): bool
    {
        if (PHP_OS_FAMILY !== 'Darwin') {
            return false;
        }
        $tmp = sys_get_temp_dir().'/rvthumb_'.md5($pdf);
        @mkdir($tmp, 0777, true);
        exec('qlmanage -t -s 900 -o '.escapeshellarg($tmp).' '.escapeshellarg($pdf).' >/dev/null 2>&1');
        $png = $tmp.'/'.basename($pdf).'.png';
        $ok = is_file($png) && filesize($png) > 5000 && copy($png, $target);
        @unlink($png);
        @rmdir($tmp);

        return $ok;
    }

    private function svgCover(array $item, string $slug): string
    {
        $palette = [['#4B3828', '#F1E8DA'], ['#6B4F3A', '#F8F3EA'], ['#8A6A4E', '#FAF6F0'], ['#3A2B1F', '#E9DDCB'], ['#A98660', '#2A2019']];
        [$bg, $fg] = $palette[crc32($slug) % count($palette)];
        $lines = array_slice(explode("\n", wordwrap($item['title'], 16, "\n", true)), 0, 6);
        $tspans = '';
        foreach ($lines as $i => $line) {
            $tspans .= sprintf('<tspan x="300" dy="%d">%s</tspan>', $i === 0 ? 0 : 58, htmlspecialchars($line, ENT_XML1));
        }
        $y = 330 - (count($lines) - 1) * 29;

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 800" width="600" height="800">'
            .'<rect width="600" height="800" fill="'.$bg.'"/>'
            .'<rect x="28" y="28" width="544" height="744" fill="none" stroke="'.$fg.'" stroke-opacity="0.55" stroke-width="2"/>'
            .'<text x="300" y="'.$y.'" text-anchor="middle" font-family="Georgia, serif" font-size="46" font-weight="600" fill="'.$fg.'">'.$tspans.'</text>'
            .'<line x1="240" y1="640" x2="360" y2="640" stroke="'.$fg.'" stroke-opacity="0.6"/>'
            .'<text x="300" y="690" text-anchor="middle" font-family="Georgia, serif" font-size="26" fill="'.$fg.'">'.htmlspecialchars($item['author'], ENT_XML1).'</text>'
            .'</svg>';
    }

    private function pageCount(string $pdf): int
    {
        if (PHP_OS_FAMILY === 'Darwin') {
            $script = 'function run(a){ObjC.import("PDFKit");var d=$.PDFDocument.alloc.initWithURL($.NSURL.fileURLWithPath(a[0]));return d?String(d.pageCount):"0";}';
            $out = trim((string) shell_exec('osascript -l JavaScript -e '.escapeshellarg($script).' '.escapeshellarg($pdf).' 2>/dev/null'));
            if (ctype_digit($out) && (int) $out > 0) {
                return (int) $out;
            }
            $md = trim((string) shell_exec('mdls -raw -name kMDItemNumberOfPages '.escapeshellarg($pdf).' 2>/dev/null'));
            if (ctype_digit($md) && (int) $md > 0) {
                return (int) $md;
            }
        }

        return 0;
    }

    private function authorId(string $name, string $bio): ?int
    {
        if (isset($this->authorCache[$name])) {
            return $this->authorCache[$name];
        }
        $existing = Author::query()->where('name', $name)->first();
        if ($existing) {
            return $this->authorCache[$name] = $existing->id;
        }

        $data = $this->authorTemplate;
        foreach (['name' => $name, 'slug' => Str::slug($name)] as $k => $v) {
            if (array_key_exists($k, $data)) {
                $data[$k] = $v;
            }
        }
        foreach (['bio', 'biography', 'description', 'about', 'introduction'] as $k) {
            if (array_key_exists($k, $data)) {
                $data[$k] = $bio;
            }
        }

        try {
            $author = new Author();
            $author->forceFill($data)->save();
            return $this->authorCache[$name] = $author->id;
        } catch (\Throwable $e) {
        }

        try {
            $minimal = ['name' => $name];
            foreach (['bio', 'biography', 'description'] as $k) {
                if (Schema::hasColumn('authors', $k)) {
                    $minimal[$k] = $bio;
                    break;
                }
            }
            $author = new Author();
            $author->forceFill($minimal)->save();
            return $this->authorCache[$name] = $author->id;
        } catch (\Throwable $e) {
        }

        return Author::query()->orderBy('id')->value('id');
    }
}
