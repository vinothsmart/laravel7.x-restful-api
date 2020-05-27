<?php

namespace App\Http\Middleware;

use Closure;

class TransfromInput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $transformer)
    {
        $transformedInput = [];
        
        // adding condition transform value to  Original value
        // For example title = name
        foreach ($request->request->all() as $input => $value) {
            $transformedInput[$transformer::originalAttribute($input)] = $value;
        }

        // Then going to replace the value
        $request->replace($transformedInput);
        
        $response = $next($request);

        if(isset($response->exception) ){
            $data = $response->getData();

            $transformedErrors = [];

            foreach ($data->error as $field => $error) {
                $transformedField =  $transformer::transformedAttribute($field);

                $transformedErrors[$transformedField]= str_replace($field, $transformedField, $error);
            }

            $data->error = $transformedErrors;

            $response->setData($data);
        }

        return $response;
    }
}
