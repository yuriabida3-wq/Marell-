<?php
namespace Database\Seeders;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder {
    public function run(): void {
        $classes = ['Class 5','Class 6','Class 7','Class 8'];
        $names = ['Brian Otieno','Mary Wanjiku','Kevin Mwangi','Faith Achieng','Peter Kamau','Grace Njeri','John Ochieng','Esther Muthoni','Samuel Kiprop','Lucy Atieno','David Njoroge','Mercy Chebet','Daniel Maina','Sarah Wangari','Joseph Odhiambo','Jane Wairimu','Michael Barasa','Ruth Nyambura','Isaac Karanja','Purity Nasimiyu'];
        foreach ($names as $i => $name) {
            $adm = sprintf('MAR-2024-%04d', $i + 1);
            $fee = 45000 + (($i % 4) * 5000);
            Student::updateOrCreate(
                ['adm_no'=>$adm],
                ['name'=>$name,'class'=>$classes[$i%4],'stream'=>['Blue','Green','Red'][$i%3],
                 'parent_name'=>'Parent of '.explode(' ', $name)[0],
                 'parent_phone'=>'2547'.str_pad((string)(10000000+$i), 8, '0', STR_PAD_LEFT),
                 'parent_email'=>'parent'.($i+1).'@example.com',
                 'total_fee'=>$fee,'paid_amount'=>0,'balance'=>$fee,'status'=>'active']
            );
        }
    }
}
