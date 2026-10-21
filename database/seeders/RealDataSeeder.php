<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Profile;
use App\Models\Category;
use App\Models\Author;
use App\Models\Ebook;
use App\Models\Review;

class RealDataSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. TẠO TÀI KHOẢN NGƯỜI DÙNG THẬT & BẢNG PROFILES
        // ==========================================
        $admin = User::create([
            'name' => 'Quản Trị Viên ReadVerse',
            'email' => 'admin@readverse.vn',
            'password' => Hash::make('Admin@123456'),
            'role' => 'admin',
        ]);
        Profile::create([
            'user_id' => $admin->id,
            'bio' => 'Quản trị viên hệ thống nền tảng Ebook ReadVerse.',
            'phone' => '0901234567',
            'address' => 'Thành phố Hồ Chí Minh, Việt Nam',
        ]);

        $authorUser1 = User::create([
            'name' => 'Nguyễn Nhật Ánh',
            'email' => 'nguyennhatanh@readverse.vn',
            'password' => Hash::make('Author@123456'),
            'role' => 'author',
        ]);
        Profile::create([
            'user_id' => $authorUser1->id,
            'bio' => 'Nhà văn chuyên viết cho tuổi mới lớn nổi tiếng tại Việt Nam.',
            'phone' => '0912345678',
            'address' => 'Quảng Nam, Việt Nam',
        ]);

        $reader1 = User::create([
            'name' => 'Lê Vũ Bảo Khang',
            'email' => 'baokhang.reader@gmail.com',
            'password' => Hash::make('User@123456'),
            'role' => 'reader',
        ]);
        Profile::create([
            'user_id' => $reader1->id,
            'bio' => 'Đam mê đọc sách công nghệ và kỹ năng sống.',
            'phone' => '0987654321',
            'address' => 'TP. Hồ Chí Minh',
        ]);

        $reader2 = User::create([
            'name' => 'Trần Minh Tâm',
            'email' => 'minhtam.dev@gmail.com',
            'password' => Hash::make('User@123456'),
            'role' => 'reader',
        ]);
        Profile::create([
            'user_id' => $reader2->id,
            'bio' => 'Lập trình viên Laravel & yêu thích sách lịch sử.',
            'phone' => '0977123456',
            'address' => 'Đà Nẵng',
        ]);

        // ==========================================
        // 2. TẠO DANH MỤC SÁCH THỰC TẾ
        // ==========================================
        $categoriesData = [
            ['name' => 'Công Nghệ Thông Tin', 'desc' => 'Sách chuyên ngành lập trình, kiến trúc phần mềm, AI và khoa học máy tính.'],
            ['name' => 'Kinh Doanh & Quản Trị', 'desc' => 'Tài chính cá nhân, khởi nghiệp, quản trị doanh nghiệp và đầu tư.'],
            ['name' => 'Tâm Lý & Kỹ Năng Sống', 'desc' => 'Thấu hiểu bản thân, phát triển tư duy, giao tiếp và quản lý thời gian.'],
            ['name' => 'Văn Học Việt Nam', 'desc' => 'Các tác phẩm văn học, tiểu thuyết và truyện ngắn kinh điển Việt Nam.'],
            ['name' => 'Khoa Học & Lịch Sử', 'desc' => 'Khám phá tri thức nhân loại, lịch sử văn minh và khoa học vũ trụ.'],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[$c['name']] = Category::create([
                'name' => $c['name'],
                'slug' => Str::slug($c['name']),
                'description' => $c['desc'],
            ]);
        }

        // ==========================================
        // 3. TẠO TÁC GIẢ THẬT (AUTHORS)
        // ==========================================
        $authorsData = [
            [
                'name' => 'Robert C. Martin',
                'bio' => 'Thường được gọi là Uncle Bob, là một kỹ sư phần mềm người Mỹ, tác giả của bộ sách kiến trúc phần mềm Clean Code nổi tiếng toàn cầu.',
            ],
            [
                'name' => 'Dale Carnegie',
                'bio' => 'Nhà văn và diễn giả người Mỹ, người phát triển các lớp huấn luyện kỹ năng giao tiếp và ứng xử nổi tiếng thế giới.',
            ],
            [
                'name' => 'Yuval Noah Harari',
                'bio' => 'Nhà lịch sử học người Israel, giáo sư Đại học Hebrew Jerusalem, tác giả của bộ trilogy lịch sử bán chạy toàn cầu.',
            ],
            [
                'name' => 'Nguyễn Nhật Ánh',
                'bio' => 'Nhà văn Việt Nam chuyên viết cho tuổi mới lớn với hàng loạt tác phẩm được chuyển thể thành phim điện ảnh ăn khách.',
            ],
            [
                'name' => 'Paulo Coelho',
                'bio' => 'Nhà văn tiểu thuyết người Brazil, tác giả của tiểu thuyết Nhà Giả Kim - một trong những cuốn sách bán chạy nhất lịch sử.',
            ],
            [
                'name' => 'Rosie Nguyễn',
                'bio' => 'Tác giả sách, blogger du lịch và người truyền cảm hứng cho thế hệ trẻ Việt Nam về tự học và trải nghiệm.',
            ],
        ];

        $authors = [];
        foreach ($authorsData as $a) {
            $authors[$a['name']] = Author::create([
                'name' => $a['name'],
                'slug' => Str::slug($a['name']),
                'bio' => $a['bio'],
            ]);
        }

        // ==========================================
        // 4. TẠO SÁCH THẬT (EBOOKS)
        // ==========================================
        $ebooksData = [
            [
                'file_path' => 'ebooks/sach1.pdf',
                'title' => 'Clean Code: Kiến Trúc Mã Nguồn Sạch',
                'author' => 'Robert C. Martin',
                'category' => 'Công Nghệ Thông Tin',
                'price' => 245000,
                'pages' => 464,
                'description' => 'Clean Code là cuốn cẩm nang kinh điển cho bất kỳ lập trình viên nào muốn nâng cao tay nghề. Sách hướng dẫn chi tiết cách đặt tên biến, viết hàm ngắn gọn, tái cấu trúc mã nguồn (refactoring) và xử lý ngoại lệ một cách chuyên nghiệp.',
                'cover_image' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=600&q=80',
            ],
            [
                'file_path' => 'ebooks/sach1.pdf',
                'title' => 'Đắc Nhân Tâm',
                'author' => 'Dale Carnegie',
                'category' => 'Tâm Lý & Kỹ Năng Sống',
                'price' => 89000,
                'pages' => 320,
                'description' => 'Đắc Nhân Tâm là cuốn sách nghệ thuật giao tiếp và thu phục lòng người kinh điển nhất mọi thời đại. Tác phẩm mang lại những nguyên tắc thực tế giúp bạn thấu hiểu tâm lý, tạo dựng mối quan hệ bền vững và thành công trong sự nghiệp.',
                'cover_image' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=600&q=80',
            ],
            [
                'file_path' => 'ebooks/sach1.pdf',
                'title' => 'Sapiens: Lược Sử Loài Người',
                'author' => 'Yuval Noah Harari',
                'category' => 'Khoa Học & Lịch Sử',
                'price' => 195000,
                'pages' => 560,
                'description' => 'Sapiens đưa người đọc vào một chuyến hành trình kinh ngạc qua toàn bộ lịch sử loài người - từ những sinh vật tầm thường sống trong cõi Đông Phi cho đến chủ nhân của hành tinh xanh ngày nay thông qua 4 cuộc cách mạng lớn.',
                'cover_image' => 'https://images.unsplash.com/photo-1457369804613-52c61a468e7d?w=600&q=80',
            ],
            [
                'file_path' => 'ebooks/sach1.pdf',
                'title' => 'Mắt Biếc',
                'author' => 'Nguyễn Nhật Ánh',
                'category' => 'Văn Học Việt Nam',
                'price' => 110000,
                'pages' => 300,
                'description' => 'Mắt Biếc là câu chuyện tình thầm lặng đầy hoài niệm của Ngạn dành cho Hà Lan - cô bạn từ thời thơ ấu có đôi mắt đẹp hút hồn. Tác phẩm đong đầy cảm xúc về làng Đo Đo và những kỷ niệm tuổi học trò không bao giờ trở lại.',
                'cover_image' => 'https://images.unsplash.com/photo-1476275466078-4007374efbbe?w=600&q=80',
            ],
            [
                'file_path' => 'ebooks/sach1.pdf',
                'title' => 'Nhà Giả Kim',
                'author' => 'Paulo Coelho',
                'category' => 'Tâm Lý & Kỹ Năng Sống',
                'price' => 79000,
                'pages' => 225,
                'description' => 'Tác phẩm kể về hành trình vượt sa mạc Sahara của chàng chăn cừu Santiago đi tìm kho báu Kim Tự Tháp. Cuốn sách gửi gắm thông điệp sâu sắc: Khi bạn quyết tâm muốn điều gì, toàn bộ vũ trụ sẽ hợp sức giúp bạn đạt được.',
                'cover_image' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=600&q=80',
            ],
            [
                'file_path' => 'ebooks/sach1.pdf',
                'title' => 'Tuổi Trẻ Đáng Giá Bao Nhiêu?',
                'author' => 'Rosie Nguyễn',
                'category' => 'Tâm Lý & Kỹ Năng Sống',
                'price' => 90000,
                'pages' => 292,
                'description' => 'Được viết từ trải nghiệm thực tế của một người đi trước, cuốn sách đúc kết những lời khuyên chân thành về 3 khía cạnh quan trọng của người trẻ: Học gì, Làm gì và Đi đâu để không lãng phí quãng đời thanh xuân.',
                'cover_image' => 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=600&q=80',
            ],
        ];

        $ebookModels = [];
        foreach ($ebooksData as $book) {
            $ebookModels[] = Ebook::create([
                'title' => $book['title'],
                'slug' => Str::slug($book['title']),
                'description' => $book['description'],
                'price' => $book['price'],
                'pages' => $book['pages'],
                'cover_image' => $book['cover_image'],
                'file_path' => 'ebooks/sample.pdf', // Tệp PDF dùng chung trong storage/app/public/ebooks
                'view_count' => rand(120, 3500),
                'status' => 'published',
                'category_id' => $categories[$book['category']]->id,
                'author_id' => $authors[$book['author']]->id,
                'user_id' => $admin->id,
            ]);
        }

        // ==========================================
        // 5. TẠO ĐÁNH GIÁ/BÌNH LUẬN THẬT (REVIEWS)
        // ==========================================
        Review::create([
            'user_id' => $reader1->id,
            'ebook_id' => $ebookModels[0]->id, // Clean Code
            'rating' => 5,
            'comment' => 'Sách cực kỳ chất lượng cho dân IT! Viết rất chi tiết về cách đặt tên hàm và chia nhỏ module code.',
        ]);

        Review::create([
            'user_id' => $reader2->id,
            'ebook_id' => $ebookModels[0]->id, // Clean Code
            'rating' => 5,
            'comment' => 'Mỗi sinh viên ngành phần mềm đều nên đọc cuốn này ít nhất một lần trong đời.',
        ]);

        Review::create([
            'user_id' => $reader1->id,
            'ebook_id' => $ebookModels[3]->id, // Mắt Biếc
            'rating' => 4,
            'comment' => 'Giọng văn Nguyễn Nhật Ánh lôi cuốn, mộc mạc nhưng kết thúc buồn quá.',
        ]);

        Review::create([
            'user_id' => $reader2->id,
            'ebook_id' => $ebookModels[2]->id, // Sapiens
            'rating' => 5,
            'comment' => 'Mở rộng tầm mắt hoàn toàn về lịch sử tiến hóa của con người. Rất đáng tiền!',
        ]);
    }
}
