<?php

class ReviewController
{
    public function getReviews()
    {
        // Dummy data for now
        $reviews = [
            [
                'name' => 'John Doe',
                'rating' => 5,
                'comment' => 'Great platform, easy to use and found my dream home in just a few days!'
            ],
            [
                'name' => 'Jane Smith',
                'rating' => 4,
                'comment' => 'The agents were very helpful and responsive. The process was smooth and hassle-free.'
            ],
            [
                'name' => 'Peter Jones',
                'rating' => 5,
                'comment' => 'I have been using this platform for a while now and I am very satisfied with the services. Highly recommended!'
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($reviews);
    }
}
