<?php

namespace App\Models;

use CodeIgniter\Model;

class CommonModel extends Model
{
    protected $table = ''; // Will be set dynamically when needed

    public function getSum($table, $column, array $conditions = [])
    {
        $builder = $this->db->table($table)->selectSum($column);
        log_message('debug', 'Calling getSum with table: ' . $table);
        if (!empty($conditions)) {
            $builder->where($conditions);
        }

        $result = $builder->get()->getRow();
        return $result ? $result->$column : 0;
    }

    public function getRow($table, array $conditions = [])
    {
        $builder = $this->db->table($table);

        if (!empty($conditions)) {
            $builder->where($conditions);
        }

        return $builder->get()->getRowArray();
    }

    public function insertRecord($table, $data)
    {
        $builder = $this->db->table($table);
        return $builder->insert($data);
    }

    public function updateRecordByWhere($table, $where, $data)
    {
        $builder = $this->db->table($table);
        $builder->where($where);
        return $builder->update($data);
    }

    public function deleteRecord($table, $where)
    {
        $builder = $this->db->table($table);
        return $builder->delete($where);
    }

    public function insertQuestionRecord($table, $data)
    {
        $builder = $this->db->table($table);
        $builder->insert($data);

        return $this->db->insertID();
    }


public function getUpcomingClassesForInstructor($instructor_id)
{
    $query = "
        SELECT 
            c.course_title, 
            c.time_zone, 
            cs.title AS section_title, 
            ll.*
        FROM courses c
        JOIN course_sections cs ON c.course_id = cs.course_id
        JOIN live_lectures ll ON cs.section_id = ll.section_id
        WHERE 
            c.instructor_id = ? 
            AND TIMESTAMP(ll.class_date, ll.class_time) >= CONVERT_TZ(NOW(), 'UTC', c.time_zone)
        ORDER BY class_date, class_time 
        LIMIT 10
    ";

    return $this->db->query($query, [$instructor_id])->getResult();
}



public function getUpcomingClassesForStudent($student_id){
    $query = "SELECT c.course_title, c.time_zone, cs.title as section_title, ll.* 
              FROM courses c 
              JOIN course_sections cs ON c.course_id = cs.course_id 
              JOIN live_lectures ll ON cs.section_id = ll.section_id 
              JOIN enrollment e ON c.course_id = e.course_id 
              WHERE e.user_id = ? 
                AND TIMESTAMP(ll.class_date, ll.class_time) >= NOW() 
              ORDER BY ll.class_date, ll.class_time 
              LIMIT 10";
    return $this->db->query($query, [$student_id])->getResult();
}


    public function getAllTransactionsOfInstructorCourses($instructor_id)
    {
        $query = "SELECT p.payment_id, p.transaction_id, p.currency, p.amount, c.course_id, c.course_title, e.enrollment_date 
                 FROM payment p 
                 JOIN enrollment e ON p.payment_id = e.payment_id 
                 JOIN courses c ON e.course_id = c.course_id 
                 WHERE c.instructor_id = ? AND e.enrollment_date <= NOW()";
        return $this->db->query($query, [$instructor_id])->getResult();
    }

    public function getLastViewedLecture($user_id){
        $query = "SELECT c.course_id, c.course_title, c.course_thumbnail, l.lecture_id, l.lecture_title, l.lecture_video_url, lh.* 
              FROM lecture_view_history lh 
              JOIN courses c ON lh.course_id = c.course_id 
              JOIN lectures l ON lh.lecture_id = l.lecture_id 
              WHERE user_id = ? 
              ORDER BY lh.updated_at DESC 
              LIMIT 1";
        return $this->db->query($query, [$user_id])->getRow();

    }


	public function getUsersByInstitute($instituteId)
{
    return $this->db->table('users')
        ->select('users.*, 
                  roles.role_name')
        ->join('roles', 'users.role_id = roles.role_id') // Join the roles table
        ->where('users.institute_id', $instituteId) // Filter by institute_id
        ->get()
        ->getResult(); // Return an array of objects
}
public function getAllSubscriptionsWithDetails(){
    return $this->db->table('subscriptions')
    ->select('subscriptions.*, subscription_plans.plan_name, subscription_plans.plan_description,subscription_plans.plan_duration,subscription_plans.plan_medium, institutes.name')
    ->join('subscription_plans', 'subscriptions.plan_id = subscription_plans.id', 'left')
    ->join('institutes', 'subscriptions.institute_id = institutes.institute_id', 'left')
    ->where('subscriptions.end_date >=', date('Y-m-d'))
    ->get()
    ->getResult(); // Returns an array of results
}
public function isCourseEnrolled($courseId,$userId)
{
    $result = $this->db->table('enrollment')
        ->where('course_id', $courseId)
        ->where('user_id', $userId)
        ->countAllResults();
    return $result; // Return 1 if enrolled, 0 if not
}

public function getSubscriptionsByInstitute($instituteId)
{
    return $this->db->table('subscriptions')
        ->select('subscriptions.*, subscription_plans.plan_name, subscription_plans.plan_description,subscription_plans.plan_duration,subscription_plans.plan_medium, institutes.name')
        ->join('subscription_plans', 'subscriptions.plan_id = subscription_plans.id', 'left')
        ->join('institutes', 'subscriptions.institute_id = institutes.institute_id', 'left')
        ->where('subscriptions.institute_id', $instituteId)
        ->where('subscriptions.end_date >=', date('Y-m-d'))
        ->get()
        ->getResult(); // Returns an array of results
}

    public function updateRecord($table, $where, $data)
    {
        $builder = $this->db->table($table);
        $builder->where($where);
        $builder->update($data);
        return true;
    }

    public function getSubscriptionById($id)
    {
        return $this->db->table('subscriptions')
            ->select('subscriptions.*, subscription_plans.plan_name,subscription_plans.plan_duration,subscription_plans.plan_medium, subscription_plans.plan_description, institutes.name')
            ->join('subscription_plans', 'subscriptions.plan_id = subscription_plans.id', 'left')
            ->join('institutes', 'subscriptions.institute_id = institutes.institute_id', 'left')
            ->where('subscriptions.id', $id)
            ->get()
            ->getRowArray(); // Returns a single result as an associative array
    }
    
    
    
    // public function getSubscriptionsByInstitute($instituteId)
    // {
    //     $query = "SELECT s.*, sp.plan_name, sp.plan_description, i.name 
    //              FROM subscriptions s 
    //              LEFT JOIN subscription_plans sp ON s.plan_id = sp.id
    //              LEFT JOIN institutes i ON s.institute_id = i.institute_id
    //              WHERE s.institute_id = ?";
        
    //     return $this->selectRecord($query, [$instituteId]);
    // }

    // public function deleteRecord($table, $where)
    // {
    //     $builder = $this->db->table($table);
    //     $builder->where($where);
    //     $builder->delete();
    //     return true;
    // }
    public function selectRecord($table, $where = array())
    {
        $builder = $this->db->table($table);
        $builder->where($where);
        $result = $builder->get();

        return $result->getResult();
    }
 public function getAllCoursesByInstitute($institute, $currencyColumn)
    {
        // Build the query
        $builder = $this->db->table('courses');
        $builder->select("
        courses.*, 
        course_pricematrix.$currencyColumn AS course_display_price, '$currencyColumn' as display_currency,
        users.first_name AS instructor_first_name,
        users.last_name AS instructor_last_name
    ");
        $builder->join('course_pricematrix', 'courses.course_price = course_pricematrix.ID', 'left');
        $builder->join('users', 'courses.instructor_id = users.user_id', 'left');
        $builder->where('courses.institute_id', $institute);
        // $builder->where('courses.course_status', 'approved');
        $builder->where('courses.course_type', '0');

        // Execute the query and return the results
        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }
    
    public function getAllCoursesAssignedByInstitute($institute, $currencyColumn)
{
    // Build the query
    $builder = $this->db->table('courses');
    $builder->select("
        courses.*, 
        course_pricematrix.$currencyColumn AS course_display_price, '$currencyColumn' as display_currency,
        users.first_name AS instructor_first_name,
        users.last_name AS instructor_last_name
    ");
    $builder->join('course_pricematrix', 'courses.course_price = course_pricematrix.ID', 'left');
    $builder->join('users', 'courses.instructor_id = users.user_id', 'left');
    $builder->where('courses.institute_id', $institute);
    // Removed: $builder->where('courses.course_type', '0');

    // Execute the query and return the results
    $result = $builder->get();
    return $result->getResultArray(); // Return as array
}



    public function getAssignedCourse($institute, $assigned_to, $currencyColumn)
    {
        // Build the query
        $builder = $this->db->table('courses as c');
        $builder->select("
            c.*, 
            cp.$currencyColumn AS course_display_price, 
            '$currencyColumn' AS display_currency,
            CONCAT(u.first_name, ' ', u.last_name) AS instructor_name,
            CONCAT(ua.first_name, ' ', ua.last_name) AS assigned_user_name
        ");
        $builder->join('course_pricematrix as cp', 'c.course_price = cp.ID', 'left');
        $builder->join('users as u', 'c.instructor_id = u.user_id', 'left');
        $builder->join('assign_course as ac', 'ac.course_id = c.course_id AND ac.institute_id = c.institute_id', 'left');
        $builder->join('users as ua', 'ac.assigned_to = ua.user_id', 'left');
    
        // Add conditions
        $builder->where('c.institute_id', $institute);
        $builder->where('ac.assigned_to', $assigned_to);
    
        // Execute the query and return the results
        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }

    public function getCourseAssignedTo($institute, $courseId)
{
    // Build the query
    $builder = $this->db->table('assign_course as ac');
    $builder->select("
        ac.id,
        ac.course_id,
        ac.institute_id,
        ac.assigned_to,
        ac.assigned_by,
        ac.assigned_at,
        CONCAT(assigned_user.first_name, ' ', assigned_user.last_name) AS assigned_to_name,
        CONCAT(assigner_user.first_name, ' ', assigner_user.last_name) AS assigned_by_name
    ");
    $builder->join('users as assigned_user', 'ac.assigned_to = assigned_user.user_id', 'left');
    $builder->join('users as assigner_user', 'ac.assigned_by = assigner_user.user_id', 'left');

    // Add conditions
    $builder->where('ac.institute_id', $institute);
    $builder->where('ac.course_id', $courseId);

    // Execute the query and return the results
    $result = $builder->get();
    return $result->getResultArray(); // Return as array
}

    

    public function getCourseByCategoryInstitute($institute, $category, $currencyColumn)
{
    // Build the query
    $builder = $this->db->table('courses');
    $builder->select("
        courses.*, 
        course_pricematrix.$currencyColumn AS course_display_price, 
        '$currencyColumn' as display_currency,
        users.first_name AS instructor_first_name,
        users.last_name AS instructor_last_name
    ");
    
    // Join with pricing table and users table
    $builder->join('course_pricematrix', 'courses.course_price = course_pricematrix.ID', 'left');
    $builder->join('users', 'courses.instructor_id = users.user_id', 'left');
    
    // Add conditions for institute and category
    $builder->where('courses.institute_id', $institute);
    $builder->where('courses.course_category_id', $category);

    // Execute the query and return the results
    $result = $builder->get();
    return $result->getResultArray(); // Return as array
}

    public function getInstituteByUserId($userId)
    {
        return $this->db->table('institutes')
            ->select('institutes.*, users.user_id')
            ->join('users', 'institutes.institute_id = users.institute_id')
            ->where('users.user_id', $userId)
            ->get()
            ->getResult(); // Return result as an array of objects
    }

public function getUserWithInstitute($userId)
{
    return $this->db->table('users')
        ->select('users.*, 
                  institutes.institute_id, 
                  institutes.name as institute_name, 
                  institutes.address, 
                  institutes.contact_number,
                  institutes.registration_number,
                  institutes.tin_number,
                  institutes.supporting_document, 
                  institutes.profile_image as institute_logo, 
                  institutes.subdomain_name,
                  institutes.institute_type') // ✅ Added institute_type here
        ->join('institutes', 'users.institute_id = institutes.institute_id')
        ->where('users.user_id', $userId)
        ->get()
        ->getRow(); // Return a single object
}


public function getOnlineClassesByInstitution($institute_id)
{
    return $this->db->table('onlineclasses')
        ->select('onlineclasses.*, 
                  users.user_id as instructor_id, 
                  users.name as instructor_name, 
                  users.email as instructor_email, 
                  users.phone as instructor_phone')
        ->join('users', 'onlineclasses.instructor_id = users.user_id')
        ->where('users.institute_id', $institute_id)
        ->get()
        ->getResult(); // Return a list of objects
}

    public function selectRecordArray($table, $where = array())
    {
        $builder = $this->db->table($table);
        $builder->where($where);
        $result = $builder->get();

        return $result->getResultArray();
    }
    public function rowRecord($table, $where = array())
    {
        $builder = $this->db->table($table);
        $builder->where($where);
        $result = $builder->get();

        return $result->getRow();
    }

   

    public function getInstructorRating($instructor_id)
{
    $query = "SELECT u.user_id, COUNT(cr.comment) AS total_reviews, IFNULL(AVG(cr.rating), 0) AS rating
              FROM `users` AS u
              LEFT JOIN courses c ON u.user_id = c.instructor_id
              LEFT JOIN course_review cr ON c.course_id = cr.course_id
              WHERE u.user_id = ?
              GROUP BY u.user_id";

    $result = $this->db->query($query, [$instructor_id])->getRow();

    // Handle false result
    if ($result === false) {
        return (object)[
            'user_id' => $instructor_id,
            'total_reviews' => 0,
            'rating' => 0.0
        ];
    }

    return $result;
}

    function getInstructorTotalCourses($instructor_id)
    {
        $query = "SELECT COUNT(*) as total_courses FROM courses WHERE instructor_id = ?";
        return $this->db->query($query, [$instructor_id])->getRow();
    }
    public function getInstructorTotalStudents($instructor_id)
    {
        $builder = $this->db->table('enrollment e')
                            ->join('courses c', 'e.course_id = c.course_id')
                            ->where('c.instructor_id', $instructor_id)
                            ->select('COUNT(*) as total_students');

        $result = $builder->get()->getRow();
        
        return $result ? (int) $result->total_students : 0;
    }

    
    
    public function getUserByEmail($email)
    {
        // Define the table you want to query
        $table = 'users';

        // Define the condition for the query
        $where = array('email' => $email);

        // Use the rowRecord method to get the user by email
        return $this->rowRecord($table, $where);
    }

    public function getUserById($email)
    {
        // Define the table you want to query
        $table = 'users';

        // Define the condition for the query
        $where = array('email' => $email);

        // Use the rowRecord method to get the user by email
        return $this->rowRecord($table, $where);
    }

    // Method to get a student with user details

    public function getCourseWithDetails($courseId, $currencyColumn)
    {
        // Build the query
        $builder = $this->db->table('courses');
        $builder->select("
            courses.*,
            course_pricematrix.$currencyColumn AS course_display_price, '$currencyColumn' as display_currency,
            users.first_name AS instructor_first_name,
            users.last_name AS instructor_last_name
        ");
        $builder->join('course_pricematrix', 'courses.course_price = course_pricematrix.ID', 'left');
        $builder->join('users', 'courses.instructor_id = users.user_id', 'left');
        $builder->where('courses.course_id', $courseId);
        // $builder->where('courses.course_status', 'approved');
        // $builder->where('courses.course_type', '0');
        // Execute the query and return the result
        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }

    public function getCourseSearch($searchterm, $currencyColumn)
    {
        // Build the query
        $builder = $this->db->table('courses');
        $builder->select("
        courses.*, 
        course_pricematrix.$currencyColumn AS course_display_price, 
        '$currencyColumn' as display_currency, 
        users.first_name AS instructor_first_name, 
        users.last_name AS instructor_last_name
    ");
    
    $builder->join('course_pricematrix', 'courses.course_price = course_pricematrix.ID', 'left');
    $builder->join('users', 'courses.instructor_id = users.user_id', 'left');
    
    // Use orLike to search both title and description columns
    $builder->groupStart()  // Start a group for the OR condition
        ->like('courses.course_title', $searchterm)
        ->orLike('courses.course_description', $searchterm);
    $builder->groupEnd(); 

    $builder->where('courses.course_status', 'approved');
    $builder->where('courses.course_type', '0');

        // Execute the query and return the result
        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }

    public function getCourseSearchInstitute($institute_id, $searchterm, $currencyColumn)
{
    // Build the query
    $builder = $this->db->table('courses');
    $builder->select("
        courses.*, 
        course_pricematrix.$currencyColumn AS course_display_price, 
        '$currencyColumn' as display_currency, 
        users.first_name AS instructor_first_name, 
        users.last_name AS instructor_last_name
    ");
    
    // Join with pricing table and users table
    $builder->join('course_pricematrix', 'courses.course_price = course_pricematrix.ID', 'left');
    $builder->join('users', 'courses.instructor_id = users.user_id', 'left');

    // Add condition to filter by institute_id
    $builder->where('courses.institute_id', $institute_id);
    $builder->where('courses.course_status', 'approved');
    $builder->where('courses.course_type', '0');
    // Add OR-like condition for search term in course_title and course_description
    $builder->groupStart()  // Start a group for the OR condition
        ->like('courses.course_title', $searchterm)
        ->orLike('courses.course_description', $searchterm);
    $builder->groupEnd(); 

    // Execute the query and return the result
    $result = $builder->get();
    return $result->getResultArray(); // Return as array
}


    public function getAllCoursesWithDetails($currencyColumn)
    {
        // Build the query
        $builder = $this->db->table('courses');
        $builder->select("
            courses.*, 
            course_pricematrix.$currencyColumn AS course_display_price, '$currencyColumn' as display_currency,
            users.first_name AS instructor_first_name,
            users.last_name AS instructor_last_name
        ");
        $builder->join('course_pricematrix', 'courses.course_price = course_pricematrix.ID', 'left');
        $builder->join('users', 'courses.instructor_id = users.user_id', 'left');
        // $builder->where('courses.course_status', 'approved');
        $builder->where('courses.course_type', '0');

        // Execute the query and return the results
        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }

    public function getAllLiveCoursesWithDetails($currencyColumn)
    {
        // Build the query
        $builder = $this->db->table('courses');
        $builder->select("
            courses.*, 
            course_pricematrix.$currencyColumn AS course_display_price, '$currencyColumn' as display_currency,
            users.first_name AS instructor_first_name,
            users.last_name AS instructor_last_name
        ");
        $builder->join('course_pricematrix', 'courses.course_price = course_pricematrix.ID', 'left');
        $builder->join('users', 'courses.instructor_id = users.user_id', 'left');
        // $builder->where('courses.course_status', 'approved');
        $builder->where('courses.course_type', '1');

        // Execute the query and return the results
        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }
        

    public function getRecomendedCourse($user_id,$currencyColumn)
    {
        // Build the query
        $builder = $this->db->table('courses');
        $builder->select("
            courses.*, 
            course_pricematrix.$currencyColumn AS course_display_price, '$currencyColumn' as display_currency,
            users.first_name AS instructor_first_name,
            users.last_name AS instructor_last_name
        ");
        $builder->join('course_pricematrix', 'courses.course_price = course_pricematrix.ID', 'left');
        $builder->join('users', 'courses.instructor_id = users.user_id', 'left');
        $builder->where('courses.course_status', 'approved');
        $builder->where('courses.course_type', '0');
        // Execute the query and return the results
        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }

    public function getRecomendedCourseCart($user_id, $currencyColumn)
    {
        // Step 1: Get course IDs and category IDs from cart
        $cartBuilder = $this->db->table('cart');
        $cartBuilder->distinct();
        $cartBuilder->select('courses.course_id, courses.course_category_id');
        $cartBuilder->join('courses', 'cart.item_id = courses.course_id');
        $cartBuilder->where('cart.user_id', $user_id); // use parameter, not hardcoded
        $cartQuery = $cartBuilder->get();
        $cartResults = $cartQuery->getResultArray();
    
        // Extract IDs
        $courseIds = array_column($cartResults, 'course_id');
        $categoryIds = array_column($cartResults, 'course_category_id');
    
        // Step 2: Fetch recommended courses
        $courseBuilder = $this->db->table('courses');
        $courseBuilder->select("
            courses.*, 
            course_pricematrix.$currencyColumn AS course_display_price, '$currencyColumn' as display_currency,
            users.first_name AS instructor_first_name,
            users.last_name AS instructor_last_name
        ");
        $courseBuilder->join('course_pricematrix', 'courses.course_price = course_pricematrix.ID', 'left');
        $courseBuilder->join('users', 'courses.instructor_id = users.user_id', 'left');
        $courseBuilder->where('courses.course_status', 'approved');
        $courseBuilder->where('courses.course_type', '0');
    
        if (!empty($courseIds)) {
            $courseBuilder->whereNotIn('courses.course_id', $courseIds);
        }
    
        if (!empty($categoryIds)) {
            $courseBuilder->whereIn('courses.course_category_id', $categoryIds);
        }
    
        // Optional: Add limit or ordering for better UX
        $courseBuilder->orderBy('courses.course_id', 'DESC');
        $courseBuilder->limit(10); // for example
    
        $query = $courseBuilder->get();
        return $query->getResultArray();
    }
    
    public function getAllCoursesByCreator($creatorId, $currencyColumn)
    {
        // Build the query
        $builder = $this->db->table('courses');
        $builder->select("
        courses.*, 
        course_pricematrix.$currencyColumn AS course_display_price, '$currencyColumn' as display_currency,
        users.first_name AS instructor_first_name,
        users.last_name AS instructor_last_name
    ");
        $builder->join('course_pricematrix', 'courses.course_price = course_pricematrix.ID', 'left');
        $builder->join('users', 'courses.instructor_id = users.user_id', 'left');
        $builder->where('courses.instructor_id', $creatorId);
        // $builder->where('courses.course_status', 'approved');
        $builder->where('courses.course_type', '0');

        // Execute the query and return the results
        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }

    public function getBankDetails(){
        $builder = $this->db->table('users u');

        $builder->select('
            u.user_id,
            CASE 
                WHEN u.role_id = 4 THEN i.name
                ELSE CONCAT(u.first_name, " ", u.last_name)
            END AS name_or_institute,
            CASE 
                WHEN u.role_id = 4 THEN "institute"
                ELSE "tutor"
            END AS user_role,
            u.email,
            u.user_status AS status,
            bd.*
        ',false);

        $builder->join('institutes i', 'u.institute_id = i.institute_id', 'left');
        $builder->join('user_bank_details bd', 'u.user_id = bd.user_id');
        $builder->where('u.deleted_at', null);

        $query = $builder->get();
        return $query->getResult();
    }
    public function getAllLiveCoursesByCreator($creatorId, $currencyColumn)
    {
        // Build the query
        $builder = $this->db->table('courses');
        $builder->select("
            courses.*,
            course_pricematrix.$currencyColumn AS course_display_price, '$currencyColumn' as display_currency,
            users.first_name AS instructor_first_name,
            users.last_name AS instructor_last_name
        ");
        $builder->join('course_pricematrix', 'courses.course_price = course_pricematrix.ID', 'left');
        $builder->join('users', 'courses.instructor_id = users.user_id', 'left');
        $builder->where('courses.instructor_id', $creatorId);
        // $builder->where('courses.course_status', 'approved');
        $builder->where('courses.course_type', '1');

        // Execute the query and return the results
        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }
    
    
    public function getAllCoursesForReview($currencyColumn)
    {
        // Build the query
        $builder = $this->db->table('courses');
        $builder->select("
        courses.*, 
        course_pricematrix.$currencyColumn AS course_display_price, '$currencyColumn' as display_currency,
        users.first_name AS instructor_first_name,
        users.last_name AS instructor_last_name
    ");
        $builder->join('course_pricematrix', 'courses.course_price = course_pricematrix.ID', 'left');
        $builder->join('users', 'courses.instructor_id = users.user_id', 'left');
        $builder->where('courses.course_status', 'requested');
        $builder->where('courses.course_type', '0');
        // Execute the query and return the results
        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }



    // Method to get a student with user details
    public function getStudentWithUser($studentId)
    {
        $builder = $this->db->table('students');
        $builder->select('students.*, users.email, users.first_name, users.last_name, users.user_status,users.profile_picture');
        $builder->join('users', 'students.user_id = users.user_id', 'left');
        $builder->where('students.student_id', $studentId);
        $result = $builder->get();
        return $result->getRowArray(); // Return as array
    }

    // Method to get all students with user details
    public function getAllStudentsWithUsers()
    {
        $builder = $this->db->table('students');
        $builder->select('students.*, users.email, users.first_name, users.last_name,users.user_status');
        $builder->join('users', 'students.user_id = users.user_id', 'left');
        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }

    public function getTutorWithUser($tutorId)
    {
        $builder = $this->db->table('users');
        $builder->select('users.*, verification_instructor.*'); // Select all columns from both tables
        $builder->join('verification_instructor', 'users.user_id = verification_instructor.user_id', 'left');
        $builder->where('users.user_id', $tutorId);
        $builder->where('users.role_id', 2); // Ensure the user has role_id = 2
        $result = $builder->get();
        return $result->getRowArray();
    }

    public function getCartDetails($userId,$currency)
{
    $builder = $this->db->table('cart');
    $builder->select("cart.cart_id, courses.*, '".$currency."' as currency, cart.date_added,$currency as display_price");
    $builder->join('courses', 'cart.item_id = courses.course_id', 'inner');
    $builder->join('course_pricematrix', 'courses.course_price = course_pricematrix.ID', 'left');
    $builder->where('cart.user_id', $userId);
    $result = $builder->get();
    return $result->getResultArray(); // Return all results as an array
}
public function getCartDetailsMobile($userId,$currency)
{
    $builder = $this->db->table('cart');
    $builder->select("cart.cart_id, courses.*, '".$currency."' as display_currency, cart.date_added,$currency as course_display_price,0 as is_enrolled,users.first_name as instructor_first_name,users.last_name as instructor_last_name");
    $builder->join('courses', 'cart.item_id = courses.course_id', 'inner');
    $builder->join('users', 'courses.instructor_id = users.user_id', 'left');
    $builder->join('course_pricematrix', 'courses.course_price = course_pricematrix.ID', 'left');
    $builder->where('cart.user_id', $userId);
    $result = $builder->get();
    return $result->getResultArray(); // Return all results as an array
}


public function getWishlistDetails($userId, $currency)
{
    $builder = $this->db->table('wishlist');
    $builder->select("wishlist.id as wishlist_id, courses.course_id, courses.course_title, '".$currency."' as currency, wishlist.created_at as date_added, $currency as display_price, courses.course_description, courses.course_thumbnail, courses.course_category_id, courses.course_language");
    $builder->join('courses', 'wishlist.course_id = courses.course_id', 'inner');
    $builder->join('course_pricematrix', 'courses.course_price = course_pricematrix.ID', 'left');
    $builder->where('wishlist.user_id', $userId);
    $result = $builder->get();
    return $result->getResultArray(); // Return all results as an array
}

public function getWishlistDetailsMobile($userId, $currency)
{
    $builder = $this->db->table('wishlist');
    $builder->select("wishlist.id as wishlist_id, courses.*, '".$currency."' as display_currency, wishlist.created_at as date_added, $currency as course_display_price, 0 as is_enrolled,users.first_name as instructor_first_name,users.last_name as instructor_last_name");
    $builder->join('courses', 'wishlist.course_id = courses.course_id', 'inner');
    $builder->join('users', 'courses.instructor_id = users.user_id', 'left');
    $builder->join('course_pricematrix', 'courses.course_price = course_pricematrix.ID', 'left');
    $builder->where('wishlist.user_id', $userId);
    $result = $builder->get();
    return $result->getResultArray(); // Return all results as an array
}

    public function getTutorWithCourseCreator($tutorId)
    {
        $builder = $this->db->table('users');
        $builder->select('users.last_name, users.first_name, users.profile_picture, verification_instructor.bio, verification_instructor.job_title'); // Select specific columns
        $builder->join('verification_instructor', 'users.user_id = verification_instructor.user_id', 'left');
        $builder->where('users.user_id', $tutorId);
        $builder->where('users.role_id', 2); // Ensure the user has role_id = 2
        $result = $builder->get();
        return $result->getRowArray();
    }



    public function getAllTutorsWithUsers()
    {
        $builder = $this->db->table('users');
        $builder->select('users.*, verification_instructor.*'); // Select all columns from both tables
        $builder->join('verification_instructor', 'users.user_id = verification_instructor.user_id', 'left');
        $builder->where('users.role_id', 2); // Ensure the user has role_id = 2
        $result = $builder->get();
        return $result->getResultArray();
    }

    public function getInstructorData($id)
    {
        $builder = $this->db->table('users');
        $builder->select('users.*, verification_instructor.*'); // Select all columns from both tables
        $builder->join('verification_instructor', 'users.user_id = verification_instructor.user_id', 'left');
        $builder->where('users.user_id', $id);
        $builder->where('users.role_id', 2); // Ensure the user has role_id = 2
        $result = $builder->get();
        return $result->getRowArray();
    }

    public function getInstructorSkills($id)
{
    $builder = $this->db->table('courses');
    $builder->distinct(); // Add DISTINCT correctly
    $builder->select('course_categories.category_name, course_categories.category_id');
    $builder->join('course_categories', 'courses.course_category_id = course_categories.category_id', 'inner');
    $builder->where('courses.instructor_id', $id);
    $result = $builder->get();
    return $result->getResultArray();
}


    public function getAllReviewsWithUsers()
    {
        $builder = $this->db->table('course_review');
        $builder->select('course_review.*, users.first_name, users.last_name');
        $builder->join('users', 'course_review.student_id = users.user_id', 'left');
        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }

    public function getAllReviewsWithUsersByCourseId($where)
    {
        $builder = $this->db->table('course_review');
        $builder->select('course_review.*, users.first_name, users.last_name,users.profile_picture');
        $builder->join('users', 'course_review.student_id = users.user_id', 'left');
        $builder->where($where);
        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }

    // Fetch records with the correct usage of the where clause

    public function getEnrollmentDetails()
    {
        $builder = $this->db->table('enrollment');
        $builder->select('CONCAT(users.first_name, " ", users.last_name) AS student_full_name, courses.course_title,payment.transaction_id, CONCAT(instructor.first_name, " ", instructor.last_name) AS instructor_full_name,enrollment.enrollment_date');
        $builder->join('users', 'enrollment.user_id = users.user_id', 'left'); // Join with the users table to get student name
        $builder->join('courses', 'enrollment.course_id = courses.course_id', 'left'); // Join with the courses table to get course details
        $builder->join('users AS instructor', 'courses.instructor_id = instructor.user_id', 'left');
        $builder->join('payment', 'payment.payment_id = enrollment.payment_id', 'left');
        // Join with the users table again for the course creator (instructor)
        // $builder->where($where); // Apply the conditions (e.g., course_id)
        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }
    public function getAllPaymentHistory()
    {
        $builder = $this->db->table('payment');
        $builder->select('payment.payment_id, CONCAT(users.first_name, " ", users.last_name) AS student_name, payment.transaction_id, payment.amount, payment.payment_date,payment.currency');
        $builder->join('users', 'payment.user_id = users.user_id', 'left'); // Join with users table to get student name
        $result = $builder->get();
        return $result->getResultArray(); // Return the result as an array
    }

    public function getAllPaymentHistoryByInstitute($institute_id)
    {
        $builder = $this->db->table('payment p');
        $builder->select('p.payment_id, p.transaction_id, p.amount, p.payment_date, p.currency, CONCAT(u.first_name, " ", u.last_name) AS student_name');
        $builder->join('users u', 'p.user_id = u.user_id');
        $builder->join('enrollment e', 'e.user_id = u.user_id');
        $builder->join('courses c', 'c.course_id = e.course_id');
        $builder->where('c.institute_id', $institute_id);
        $result = $builder->get();
        return $result->getResultArray(); // Return the result as an array
    }
    public function getCourseByPaymentId($paymentId)
    {
        $builder = $this->db->table('enrollment');
        $builder->select('courses.course_title, courses.instructor_id, CONCAT(users.first_name, " ", users.last_name) AS instructor_name, enrollment.enrollment_date');
        $builder->join('courses', 'enrollment.course_id = courses.course_id', 'left'); // Join with courses table
        $builder->join('users', 'users.user_id = courses.instructor_id', 'left'); // Join with users table to get instructor details
        $builder->where('enrollment.payment_id', $paymentId); // Filter by payment_id
        $result = $builder->get();
        return $result->getResultArray(); // Return the single row as an array
    }

    public function getEnrollmentsByCourseId($courseId)
    {
        $builder = $this->db->table('enrollment');
        $builder->select('CONCAT(users.first_name, " ", users.last_name) AS student_full_name, courses.course_title, payment.transaction_id, CONCAT(instructor.first_name, " ", instructor.last_name) AS instructor_full_name, enrollment.enrollment_date');
        $builder->join('users', 'enrollment.user_id = users.user_id', 'left'); // Join with the users table to get student name
        $builder->join('courses', 'enrollment.course_id = courses.course_id', 'left'); // Join with the courses table to get course details
        $builder->join('users AS instructor', 'courses.instructor_id = instructor.user_id', 'left');
        $builder->join('payment', 'payment.payment_id = enrollment.payment_id', 'left');
        $builder->where('enrollment.course_id', $courseId); // Filter by course_id

        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }

    public function getEnrollmentsByStudentId($userId)
    {
        $builder = $this->db->table('enrollment');
        $builder->select('CONCAT(users.first_name, " ", users.last_name) AS student_full_name,courses.course_id, courses.course_title,courses.course_description,courses.course_thumbnail, payment.transaction_id, CONCAT(instructor.first_name, " ", instructor.last_name) AS instructor_full_name, enrollment.enrollment_date');
        $builder->join('users', 'enrollment.user_id = users.user_id', 'left'); // Join with the users table to get student name
        $builder->join('courses', 'enrollment.course_id = courses.course_id', 'left'); // Join with the courses table to get course details
        $builder->join('users AS instructor', 'courses.instructor_id = instructor.user_id', 'left');
        $builder->join('payment', 'payment.payment_id = enrollment.payment_id', 'left');
        $builder->where('enrollment.user_id', $userId); 
        $builder->where('courses.course_type', '0');
        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }
    public function getEnrollmentsByStudentIdLiveCourses($userId)
    {
        $builder = $this->db->table('enrollment');
        $builder->select('CONCAT(users.first_name, " ", users.last_name) AS student_full_name,courses.*, payment.transaction_id, enrollment.enrollment_date,users.first_name as instructor_first_name,users.last_name as instructor_last_name,1 as is_enrolled');
        $builder->join('users', 'enrollment.user_id = users.user_id', 'left'); // Join with the users table to get student name 
        $builder->join('courses', 'enrollment.course_id = courses.course_id', 'left'); // Join with the courses table to get course details
        $builder->join('users AS instructor', 'courses.instructor_id = instructor.user_id', 'left');
        $builder->join('payment', 'payment.payment_id = enrollment.payment_id', 'left');
        $builder->where('enrollment.user_id', $userId); // Filter by user_id
        $builder->where('courses.course_type', '1');
        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }
    public function getAllDonations()
    {
        $builder = $this->db->table('donations');
        $builder->select('*');
        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }
 public function getEnrollmentsByStudentIdMobile($userId)
{
    $builder = $this->db->table('enrollment');
    $builder->select('
        CONCAT(u.first_name, " ", u.last_name) AS student_full_name,
        c.*,
        p.transaction_id,
        enrollment.enrollment_date,
        instructor.first_name as instructor_first_name,
        instructor.last_name as instructor_last_name,
        1 as is_enrolled
    ');
    $builder->join('users u', 'enrollment.user_id = u.user_id', 'left');
    $builder->join('courses c', 'enrollment.course_id = c.course_id', 'inner'); // inner join to avoid null courses
    $builder->join('users AS instructor', 'c.instructor_id = instructor.user_id', 'left');
    $builder->join('payment p', 'p.payment_id = enrollment.payment_id', 'left');
    $builder->where('enrollment.user_id', $userId);

    $result = $builder->get();
    return $result->getResultArray();
}

    public function getEnrollmentsByStudentIdMobileLiveCourses($userId)
    {
        $builder = $this->db->table('enrollment');
        $builder->select('CONCAT(users.first_name, " ", users.last_name) AS student_full_name,courses.*, payment.transaction_id, enrollment.enrollment_date,users.first_name as instructor_first_name,users.last_name as instructor_last_name,1 as is_enrolled');
        $builder->join('users', 'enrollment.user_id = users.user_id', 'left'); // Join with the users table to get student name
        $builder->join('courses', 'enrollment.course_id = courses.course_id', 'left'); // Join with the courses table to get course details
        $builder->join('users AS instructor', 'courses.instructor_id = instructor.user_id', 'left');
        $builder->join('payment', 'payment.payment_id = enrollment.payment_id', 'left');
        $builder->where('enrollment.user_id', $userId); // Filter by user_id
        $builder->where('courses.course_type', '1');
        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }

    public function getEnrollmentsByStudentIdInstitute($userId, $institute_id)
    {
        $builder = $this->db->table('enrollment');
        $builder->select('CONCAT(users.first_name, " ", users.last_name) AS student_full_name,courses.course_id, courses.course_title,courses.course_description,courses.course_thumbnail, payment.transaction_id, CONCAT(instructor.first_name, " ", instructor.last_name) AS instructor_full_name, enrollment.enrollment_date');
        $builder->join('users', 'enrollment.user_id = users.user_id', 'left'); // Join with the users table to get student name
        $builder->join('courses', 'enrollment.course_id = courses.course_id', 'left'); // Join with the courses table to get course details
        $builder->join('users AS instructor', 'courses.instructor_id = instructor.user_id', 'left');
        $builder->join('payment', 'payment.payment_id = enrollment.payment_id', 'left');
        $builder->where('enrollment.user_id', $userId); // Filter by user_id
        $builder->where('users.institute_id', $institute_id);

        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }

    public function getEnrollmentsByInstructorId($userId)
    {
        $builder = $this->db->table('enrollment');
        $builder->select('CONCAT(users.first_name, " ", users.last_name) AS student_full_name, courses.course_title, payment.transaction_id, CONCAT(instructor.first_name, " ", instructor.last_name) AS instructor_full_name, enrollment.enrollment_date');
        $builder->join('users', 'enrollment.user_id = users.user_id', 'left'); // Join with the users table to get student name
        $builder->join('courses', 'enrollment.course_id = courses.course_id', 'left'); // Join with the courses table to get course details
        $builder->join('users AS instructor', 'courses.instructor_id = instructor.user_id', 'left');
        $builder->join('payment', 'payment.payment_id = enrollment.payment_id', 'left');
        $builder->where('courses.instructor_id', $userId); // Filter by user_id

        $result = $builder->get();
        return $result->getResultArray(); // Return as array
    }



    // You can add similar methods for other joins if needed   
    public function verificationRowRecord($code)
    {
        // Define the current time to compare with expiration
        $currentDateTime = date('Y-m-d H:i:s');


        // Query the model to find the record with the provided code
        $record = $this->db->table('users')
            ->where('verification_code', $code)
            ->where('expires_at >=', $currentDateTime);
        //    ->first();


        return $record;
    }

public function getCount(string $table, array $conditions = [])
    {
        $builder = $this->db->table($table);
log_message('debug', 'Calling getSum with table: ' . $table);        
if (!empty($conditions)) {
            $builder->where($conditions);
        }
        return $builder->countAllResults();
    }

    public function getTotalCounts()
{
    // Query to get total counts
    $totalUsers = $this->db->table('users')->countAllResults();
    $totalinstructors = $this->db->table('users')
    ->where('role_id', 2)
    ->countAllResults();
    $total_students = $this->db->table('users')
    ->where('role_id', 3)
    ->countAllResults();
    $total_institutes = $this->db->table('users')
    ->where('role_id', 4)
    ->countAllResults();
    $totalCourses = $this->db->table('courses')->countAllResults();
    $totalEnrollments = $this->db->table('enrollment')->countAllResults();

    return [
        'total_users' => $totalUsers,
        'total_courses' => $totalCourses,
        'total_enrollments' => $totalEnrollments,
        'total_institutes' => $total_institutes,
        'total_instructors' => $totalinstructors,
        'total_students' => $total_students
    ];
}

public function getTotalCountsByInstitution($institute_id)
{
    // Query to get total counts
    $totalUsers = $this->db->table('users')->where('institute_id', $institute_id)->countAllResults();
    $totalinstructors = $this->db->table('users')
    ->where('role_id', 2)
    ->where('institute_id', $institute_id)
    ->countAllResults();
    $total_students = $this->db->table('users')
    ->where('role_id', 3)
    ->where('institute_id', $institute_id)
    ->countAllResults();
    // $totalCourses = $this->db->table('courses')
    // ->where('institute_id', $institute_id)->countAllResults();
    // $totalEnrollments = $this->db->table('enrollment')->countAllResults();

    return [
        // 'total_courses' => $totalCourses,
        'total_users' => $totalUsers,
        'total_instructors' => $totalinstructors,
        'total_students' => $total_students
    ];
}

public function getTotalRevenue($currency = 'USD')
{
    $builder = $this->db->table('payment');
    $builder->selectSum('amount')->where('currency', $currency);
    $result = $builder->get()->getRow();

    return ['total_revenue' => $result->amount, 'currency' => $currency];
}
public function getAverageCourseRatings()
{
    $builder = $this->db->table('course_review');
    $builder->select('course_id, AVG(rating) as average_rating');
    $builder->groupBy('course_id');
    $result = $builder->get();

    return $result->getResultArray();
}
public function getMonthlyEnrollmentTrends()
{
    $builder = $this->db->table('enrollment');
    $builder->select("DATE_FORMAT(enrollment_date, '%Y-%m') as month, COUNT(*) as enrollments");
    $builder->groupBy("month");
    $builder->orderBy("month", 'ASC');
    $result = $builder->get();

    return $result->getResultArray();
}
public function getCourseEnrollmentStatus()
{
    $builder = $this->db->table('enrollment');
    $builder->select('course_id, 
                      SUM(CASE WHEN enrollment_status = "active" THEN 1 ELSE 0 END) as active_enrollments, 
                      SUM(CASE WHEN enrollment_status = "completed" THEN 1 ELSE 0 END) as completed_enrollments');
    $builder->groupBy('course_id');
    $result = $builder->get();

    return $result->getResultArray();
}

public function getStudentsEnrolledInInstructorCourses($instructorId)
{
    // Build the query
    $query = $this->db->table('courses c')
        ->select('c.course_id, c.course_title,u.user_id, u.first_name, u.last_name, u.role_id,u.email, u.profile_picture, e.enrollment_date')
        ->join('enrollment e', 'c.course_id = e.course_id')
        ->join('users u', 'e.user_id = u.user_id')
        ->where('c.instructor_id', $instructorId)
        ->get();
    // Return the result as an array
    return $query->getResultArray();
}

public function getDistinctStudentsByInstructor($instructorId)
{
    $query = $this->db->table('users u')
        ->distinct()
        ->select('u.user_id, u.role_id') // Combine columns in one string
        ->join('enrollment e', 'u.user_id = e.user_id')
        ->join('courses c', 'c.course_id = e.course_id')
        ->where('c.instructor_id', $instructorId)
        ->get();

    return $query->getResultArray();
}

public function getNotificationsWithUserDetails()
{
    $builder = $this->db->table('notifications n');
    $builder->select('n.*, 
                      CONCAT(us.first_name, " ", us.last_name) AS sender_name, 
                      us.profile_picture AS sender_profile, 
                      CONCAT(ur.first_name, " ", ur.last_name) AS receiver_name, 
                      ur.profile_picture AS receiver_profile');
    $builder->join('users us', 'n.sender_id = us.user_id');
    $builder->join('users ur', 'n.receiver_id = ur.user_id');

    $query = $builder->get();
    return $query->getResultArray(); // or getResult() if you want objects
}

// public function getNotificationsBySenderId($senderId)
// {
//     $builder = $this->db->table('notifications n');
//     $builder->select('n.*, 
//                       CONCAT(us.first_name, " ", us.last_name) AS sender_name, 
//                       us.profile_picture AS sender_profile, 
//                       CONCAT(ur.first_name, " ", ur.last_name) AS receiver_name, 
//                       ur.profile_picture AS receiver_profile');
//     $builder->join('users us', 'n.sender_id = us.user_id');
//     $builder->join('users ur', 'n.receiver_id = ur.user_id');
//     $builder->where('n.sender_id', $senderId);

//     $query = $builder->get();
//     return $query->getResultArray();
// }

public function getNotificationsBySenderId($senderId)
{
    $builder = $this->db->table('notifications');
    $builder->select('DISTINCT title, message, sender_id, sent_to ,created_at', false);
    $builder->where('sender_id', $senderId);
    $builder->orderBy('created_at', 'DESC');
    $query = $builder->get();
    return $query->getResultArray();
}



public function getNotificationsByReceiverId($receiverId)
{
    $builder = $this->db->table('notification_receivers nr');
    $builder->select('
        nr.id AS receiver_entry_id,
        nr.is_read,
        nr.created_at AS received_at,
        n.id AS notification_id,
        n.sender_id,
        n.role_id,
        n.title,
        n.message,
        n.type,
        n.sent_to,
        n.created_at AS sent_at,
        CONCAT(us.first_name, " ", us.last_name) AS sender_name,
        us.profile_picture AS sender_profile,
        CONCAT(ur.first_name, " ", ur.last_name) AS receiver_name,
        ur.profile_picture AS receiver_profile
    ');
    $builder->join('notifications n', 'n.id = nr.notification_id');
    $builder->join('users us', 'n.sender_id = us.user_id');
    $builder->join('users ur', 'nr.receiver_id = ur.user_id');
    $builder->where('nr.receiver_id', $receiverId);
    $builder->orderBy('nr.created_at', 'DESC');

    $query = $builder->get();
    return $query->getResultArray();
}

public function getTutorCourses($tutorId)
{
    return $this->db->table('courses')
        ->select('course_id, course_title, course_description, instructor_id, course_category_id, course_language, course_price, course_level, course_thumbnail, course_intro_video, start_date, end_date, class_timing, revenue_share, created_at, updated_at, deleted_at, course_status, is_published, institute_id, is_public, is_best_seller, course_type, time_zone')
        ->where('instructor_id', $tutorId)  // Fetch courses assigned to the tutor
        ->get()->getResult();
}
public function getStudentsByCourses($courseIds)
{
    return $this->db->table('enrollments')
        ->select('enrollment_id, user_id, course_id, payment_id, enrollment_date, progress, status, users.first_name, users.last_name, users.email, users.phone_num, users.profile_picture, users.user_status')
        ->join('users', 'users.user_id = enrollments.student_id')
        ->whereIn('enrollments.course_id', $courseIds) // Filter by the provided course IDs
        ->where('users.role_id', 3) // Ensure users are students (role_id 3)
        ->get()->getResult();
}

public function countStudentsByInstituteTutor($instructorId, $instituteId) 
{
    return $this->db->table('enrollment e')
        ->join('courses c', 'e.course_id = c.course_id')   // <-- fixed here
        ->where('c.instructor_id', $instructorId)
        ->where('c.institute_id', $instituteId)
        ->countAllResults();
}

public function countStudentsByInstructor($instructorId)
{
    return $this->db->table('enrollment e')
        ->join('courses c', 'e.course_id = c.course_id')
        ->where('c.instructor_id', $instructorId)
        ->countAllResults();
}

public function getTotalCoursesByInstitute($instituteId)
{
    return $this->db->table('courses')
        ->where('institute_id', $instituteId)
        ->where('deleted_at', null)
        ->countAllResults();
}

public function getTotalEnrollmentsByInstitute($instituteId)
{
    $builder = $this->db->table('enrollment e');
    $builder->join('courses c', 'e.course_id = c.course_id');
    $builder->where('c.institute_id', $instituteId);
    $builder->where('e.deleted_at', null);
    $builder->where('c.deleted_at', null);
    return $builder->countAllResults();
}

public function getTotalStudentsByInstitute($instituteId)
{
    $builder = $this->db->table('enrollment e');
    $builder->join('courses c', 'e.course_id = c.course_id');
    $builder->where('c.institute_id', $instituteId);
    $builder->where('e.deleted_at', null);
    $builder->where('c.deleted_at', null);
    $builder->select('COUNT(DISTINCT e.user_id) AS totalStudents');
    $query = $builder->get();
    $result = $query->getRow();
    return $result ? $result->totalStudents : 0;
}

public function getTotalTutorsByInstitute($instituteId, $tutorRoleId = 2)
{
    return $this->db->table('users')
        ->where('institute_id', $instituteId)
        ->where('role_id', $tutorRoleId)
        ->where('deleted_at', null)
        ->countAllResults();
}

public function countStudentsByInstitute($instituteId)
{
    return $this->db->table('enrollment e')
        ->join('courses c', 'c.course_id = e.course_id')
        ->where('c.instructor_id', $instituteId)
        ->where('e.status', 1)
        ->countAllResults();
}

public function getTokensByUserIds(array $userIds)
{
    $builder = $this->db->table('firebase_device_tokens');
    $builder->select('token');
    $builder->whereIn('user_id', $userIds);
    $builder->where('is_active', 1);

    $result = $builder->get()->getResultArray();

    return array_column($result, 'token');
}

 public function getTotalCourseDuration($courseId)
{
    return $this->db->table('lectures')
        ->select('SUM(lectures.lecture_duration) AS total_duration')
        ->join('course_sections', 'course_sections.section_id = lectures.section_id')
        ->where('course_sections.course_id', $courseId)
        ->where('course_sections.deleted_at IS NULL')
        ->where('lectures.deleted_at IS NULL')
        ->get()
        ->getRowArray(); // returns associative array
}


}
