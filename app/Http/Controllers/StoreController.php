<?php

namespace App\Http\Controllers;

/**
 * Trendy Closet storefront — data adapted from the "Storefront Explorations"
 * design doc. Product imagery is served from Unsplash via the img() helper.
 */
class StoreController extends Controller
{
    /**
     * Build an Unsplash image descriptor (url + credit) used throughout the store.
     */
    private function img(string $id, string $author, string $slug, int $w = 900): array
    {
        return [
            'img' => "https://images.unsplash.com/{$id}?q=60&w={$w}&auto=format&fit=crop",
            'credit' => "Photo by {$author} on Unsplash",
            'credit_href' => "https://unsplash.com/@{$slug}",
        ];
    }

    private function services(): array
    {
        return [
            ['icon' => '🚚', 'title' => 'Free Shipping', 'sub' => 'On orders over $150'],
            ['icon' => '↩', 'title' => 'Easy Returns', 'sub' => '30-day return policy'],
            ['icon' => '🔒', 'title' => 'Secure Payment', 'sub' => 'SSL encrypted checkout'],
            ['icon' => '💬', 'title' => 'Personal Styling', 'sub' => 'DM Leila for advice'],
        ];
    }

    private function cartItems(): array
    {
        return [
            ['name' => 'Leila Wrap Midi Dress — Sage', 'meta' => 'Size S · Sage', 'qty' => 1, 'total' => '$54.00'] + $this->img('photo-1578632292335-df3abbb0d586', 'Max Titov', 'fearvi'),
            ['name' => 'Oversized Linen Shirt — Ecru', 'meta' => 'Size M · Ecru', 'qty' => 1, 'total' => '$42.00'] + $this->img('photo-1619086303291-0ef7699e4b31', 'Vladimir Yelizarov', 'yelizarov'),
            ['name' => 'Kids Cotton Playset — Clay', 'meta' => 'Age 4–5 · Clay', 'qty' => 1, 'total' => '$38.00'] + $this->img('photo-1590480598135-3be152c87913', 'Kin Li', 'kinli'),
        ];
    }

    public function home()
    {
        $categories = [
            ['name' => 'Dresses', 'count' => '24 products'] + $this->img('photo-1568252542512-9fe8fe9c87bb', 'Khaled Ghareeb', 'khaledkagii'),
            ['name' => 'Tops & Shirts', 'count' => '31 products'] + $this->img('photo-1489987707025-afc232f7ea0f', 'Parker Burchfield', 'parkerburchfield'),
            ['name' => 'Menswear', 'count' => '18 products'] + $this->img('photo-1441984904996-e0b6ba687e04', 'Clark Street Mercantile', 'mercantile'),
            ['name' => 'Kids', 'count' => '15 products'] + $this->img('photo-1578897367107-2828e351c8a8', 'Kayan Baby', 'kayanbaby'),
            ['name' => 'Bags', 'count' => '12 products'] + $this->img('photo-1525507119028-ed4c629a60a3', 'Junko Nakase', 'pao_note'),
            ['name' => 'Accessories', 'count' => '20 products'] + $this->img('photo-1562572159-4efc207f5aff', 'Napat Saeng', 'napats'),
            ['name' => 'Knitwear', 'count' => '14 products'] + $this->img('photo-1490481651871-ab68de25d43d', 'Priscilla Du Preez', 'priscilladupreez'),
            ['name' => 'Shoes', 'count' => '17 products'] + $this->img('photo-1560769629-975ec94e6a86', 'Jakob Owens', 'jakobowens1'),
            ['name' => 'Sale', 'count' => '40+ products'] + $this->img('photo-1470309864661-68328b2cd0a5', 'Artificial Photography', 'artificialphotography'),
        ];

        $featured = [
            ['name' => 'Leila Wrap Midi Dress — Sage', 'badge' => '-15%', 'was' => '$64', 'now' => '$54', 'stars' => 5] + $this->img('photo-1578632292335-df3abbb0d586', 'Max Titov', 'fearvi'),
            ['name' => 'Oversized Linen Shirt — Ecru', 'badge' => 'NEW', 'was' => '', 'now' => '$42', 'stars' => 5] + $this->img('photo-1619086303291-0ef7699e4b31', 'Vladimir Yelizarov', 'yelizarov'),
            ['name' => 'High-Waist Wide Leg Trousers', 'badge' => '-10%', 'was' => '$58', 'now' => '$52', 'stars' => 5] + $this->img('photo-1627577279497-4b24bf1021b6', 'Helen Ast', 'helenaast'),
            ['name' => 'Kids Cotton Playset — Clay', 'badge' => 'NEW', 'was' => '', 'now' => '$28', 'stars' => 5] + $this->img('photo-1590480598135-3be152c87913', 'Kin Li', 'kinli'),
            ['name' => 'Satin Slip Skirt — Champagne', 'badge' => 'NEW', 'was' => '', 'now' => '$44', 'stars' => 5] + $this->img('photo-1495121605193-b116b5b9c5fe', 'Alexandra Gorn', 'alexagorn'),
            ['name' => 'Belted Shirt Dress — Sand', 'badge' => '', 'was' => '', 'now' => '$56', 'stars' => 5] + $this->img('photo-1627292441194-0280c19e74e4', 'Ahmad Ebadi', 'ebadi__ahmad'),
            ['name' => 'Knit Cardigan — Oat', 'badge' => '-10%', 'was' => '$52', 'now' => '$47', 'stars' => 4] + $this->img('photo-1490481651871-ab68de25d43d', 'Priscilla Du Preez', 'priscilladupreez'),
            ['name' => 'Everyday Tote — Natural', 'badge' => 'NEW', 'was' => '', 'now' => '$30', 'stars' => 5] + $this->img('photo-1516762689617-e1cffcef479d', 'Heather Ford', 'the_modern_life_mrs'),
        ];

        $countdown = [
            ['n' => '04', 'l' => 'DAYS'], ['n' => '16', 'l' => 'HOURS'], ['n' => '45', 'l' => 'MINS'], ['n' => '12', 'l' => 'SECS'],
        ];

        $deals = [
            ['name' => 'Ruffle Neck Blouse — Sky', 'badge' => '-20%', 'was' => '$46', 'now' => '$37', 'stars' => 4] + $this->img('photo-1619785292559-a15caa28bde6', 'Adele Shafiee', 'adeleshafiee'),
            ['name' => 'Everyday Tote — Natural', 'badge' => '-25%', 'was' => '$40', 'now' => '$30', 'stars' => 4] + $this->img('photo-1516762689617-e1cffcef479d', 'Heather Ford', 'the_modern_life_mrs'),
            ['name' => 'Men Crew Sweatshirt — Stone', 'badge' => '-15%', 'was' => '$48', 'now' => '$41', 'stars' => 4] + $this->img('photo-1578681994506-b8f463449011', 'JUSTIN BUISSON', 'justinbuisson'),
            ['name' => 'Round Frame Sunglasses', 'badge' => '-30%', 'was' => '$26', 'now' => '$18', 'stars' => 4] + $this->img('photo-1529139574466-a303027c1d8b', 'Katsiaryna Endruszkiewicz', 'endka_1'),
            ['name' => 'Boxy Crop Tee — White', 'badge' => '-20%', 'was' => '$30', 'now' => '$24', 'stars' => 4] + $this->img('photo-1521572163474-6864f9cf17ab', 'Anomaly', 'anomaly'),
            ['name' => 'Leila Wrap Midi Dress — Sage', 'badge' => '-15%', 'was' => '$64', 'now' => '$54', 'stars' => 5] + $this->img('photo-1578632292335-df3abbb0d586', 'Max Titov', 'fearvi'),
            ['name' => 'Belted Shirt Dress — Sand', 'badge' => '-20%', 'was' => '$70', 'now' => '$56', 'stars' => 4] + $this->img('photo-1627292441194-0280c19e74e4', 'Ahmad Ebadi', 'ebadi__ahmad'),
            ['name' => 'High-Waist Wide Leg Trousers', 'badge' => '-10%', 'was' => '$58', 'now' => '$52', 'stars' => 5] + $this->img('photo-1627577279497-4b24bf1021b6', 'Helen Ast', 'helenaast'),
        ];

        $instagram = [
            $this->img('photo-1613915617430-8ab0fd7c6baf', 'Chyntia Juls', 'chyntiajuls', 400),
            $this->img('photo-1629374029669-aab2f060553b', 'Edoardo Cuoghi', 'edoardo_cuoghi_98', 400),
            $this->img('photo-1534880786429-7cb3199b7b0f', 'Daiga Ellaby', 'daiga_ellaby', 400),
            $this->img('photo-1541519481457-763224276691', 'Alexander Krivitskiy', 'krivitskiy', 400),
        ];

        $services = $this->services();
        $hero = $this->img('photo-1611042553484-d61f84d22784', 'Raamin ka', 'raaminka', 1200);
        $heroDetail = $this->img('photo-1544441893-675973e31985', 'Mnz', 'mnzoutfits', 500);
        $megaFeat = $this->img('photo-1567401893414-76b7b1e5a7a5', 'Burgess Milner', 'burgessbadass', 700);
        $promo1 = $this->img('photo-1652184513381-9755426e7fd2', 'Edward Howell', 'edwardhowellphotography', 1000);
        $promo2 = $this->img('photo-1519238263530-99bdd11df2ea', 'Terricks Noah', 'major001', 1000);

        return view('store.home', compact(
            'services', 'categories', 'featured', 'countdown', 'deals', 'instagram',
            'hero', 'heroDetail', 'megaFeat', 'promo1', 'promo2'
        ) + ['active' => 'home', 'bagCount' => 0, 'bagTotal' => '$0.00']);
    }

    public function listing()
    {
        $filterCats = [
            ['name' => 'Dresses', 'n' => 24], ['name' => 'Tops & Shirts', 'n' => 31], ['name' => 'Trousers & Skirts', 'n' => 17],
            ['name' => 'Knitwear', 'n' => 9], ['name' => 'Bags', 'n' => 12], ['name' => 'Accessories', 'n' => 20],
        ];

        $sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL'];

        $listing = [
            ['name' => 'Leila Wrap Midi Dress — Sage', 'badge' => '-15%', 'stars' => 5, 'was' => '$64', 'now' => '$54'] + $this->img('photo-1578632292335-df3abbb0d586', 'Max Titov', 'fearvi'),
            ['name' => 'Oversized Linen Shirt — Ecru', 'badge' => 'NEW', 'stars' => 5, 'was' => '', 'now' => '$42'] + $this->img('photo-1619086303291-0ef7699e4b31', 'Vladimir Yelizarov', 'yelizarov'),
            ['name' => 'High-Waist Wide Leg Trousers', 'badge' => '', 'stars' => 4, 'was' => '$58', 'now' => '$52'] + $this->img('photo-1627577279497-4b24bf1021b6', 'Helen Ast', 'helenaast'),
            ['name' => 'Ruffle Neck Blouse — Sky', 'badge' => '-20%', 'stars' => 4, 'was' => '$46', 'now' => '$37'] + $this->img('photo-1619785292559-a15caa28bde6', 'Adele Shafiee', 'adeleshafiee'),
            ['name' => 'Satin Slip Skirt — Champagne', 'badge' => 'NEW', 'stars' => 5, 'was' => '', 'now' => '$44'] + $this->img('photo-1495121605193-b116b5b9c5fe', 'Alexandra Gorn', 'alexagorn'),
            ['name' => 'Boxy Crop Tee — White', 'badge' => '', 'stars' => 4, 'was' => '', 'now' => '$24'] + $this->img('photo-1521572163474-6864f9cf17ab', 'Anomaly', 'anomaly'),
            ['name' => 'Belted Shirt Dress — Sand', 'badge' => '', 'stars' => 5, 'was' => '', 'now' => '$56'] + $this->img('photo-1627292441194-0280c19e74e4', 'Ahmad Ebadi', 'ebadi__ahmad'),
            ['name' => 'Knit Cardigan — Oat', 'badge' => '-10%', 'stars' => 4, 'was' => '$52', 'now' => '$47'] + $this->img('photo-1490481651871-ab68de25d43d', 'Priscilla Du Preez', 'priscilladupreez'),
        ];

        $sideBanner = $this->img('photo-1470309864661-68328b2cd0a5', 'Artificial Photography', 'artificialphotography', 600);

        return view('store.listing', compact('filterCats', 'sizes', 'listing', 'sideBanner')
            + ['active' => 'women', 'bagCount' => 1, 'bagTotal' => '$54.00']);
    }

    public function product()
    {
        $sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL'];

        $gallery = [
            $this->img('photo-1578632292335-df3abbb0d586', 'Max Titov', 'fearvi', 300),
            $this->img('photo-1652184513381-9755426e7fd2', 'Edward Howell', 'edwardhowellphotography', 300),
            $this->img('photo-1495121605193-b116b5b9c5fe', 'Alexandra Gorn', 'alexagorn', 300),
            $this->img('photo-1516762689617-e1cffcef479d', 'Heather Ford', 'the_modern_life_mrs', 300),
        ];

        $main = $this->img('photo-1578632292335-df3abbb0d586', 'Max Titov', 'fearvi', 1000);

        $related = [
            ['name' => 'Satin Slip Skirt — Champagne', 'now' => '$44'] + $this->img('photo-1495121605193-b116b5b9c5fe', 'Alexandra Gorn', 'alexagorn'),
            ['name' => 'Ruffle Neck Blouse — Sky', 'now' => '$37'] + $this->img('photo-1619785292559-a15caa28bde6', 'Adele Shafiee', 'adeleshafiee'),
            ['name' => 'Belted Shirt Dress — Sand', 'now' => '$56'] + $this->img('photo-1627292441194-0280c19e74e4', 'Ahmad Ebadi', 'ebadi__ahmad'),
            ['name' => 'Everyday Tote — Natural', 'now' => '$30'] + $this->img('photo-1516762689617-e1cffcef479d', 'Heather Ford', 'the_modern_life_mrs'),
        ];

        return view('store.product', compact('sizes', 'gallery', 'main', 'related')
            + ['active' => 'women', 'bagCount' => 1, 'bagTotal' => '$54.00']);
    }

    public function cart()
    {
        return view('store.cart', ['cart' => $this->cartItems(), 'active' => null, 'bagCount' => 3, 'bagTotal' => '$134.00']);
    }

    public function checkout()
    {
        return view('store.checkout', ['cart' => $this->cartItems(), 'active' => null, 'bagCount' => 3, 'bagTotal' => '$134.00']);
    }

    public function about()
    {
        $hero = $this->img('photo-1490481651871-ab68de25d43d', 'Priscilla Du Preez', 'priscilladupreez', 1400);
        $portrait = $this->img('photo-1544441893-675973e31985', 'Mnz', 'mnzoutfits', 800);

        return view('store.about', compact('hero', 'portrait')
            + ['active' => 'about', 'bagCount' => 0, 'bagTotal' => '$0.00']);
    }

    public function contact()
    {
        return view('store.contact', ['active' => 'contact', 'bagCount' => 0, 'bagTotal' => '$0.00']);
    }

    public function policies()
    {
        return view('store.policies', ['active' => 'policies', 'bagCount' => 0, 'bagTotal' => '$0.00']);
    }
}
