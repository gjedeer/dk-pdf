<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
* displays pdf button
*/
function dkpdf_display_pdf_button( $content ) {

  // if is generated pdf don't show pdf button
  $pdf = get_query_var( 'pdf' );

  if ( $pdf ) { 

      // remove dkpdf-button shortcode
      // TODO implement logic: if exists    
      remove_shortcode('dkpdf-button');
      $content = str_replace( "[dkpdf-button]", "", $content );

      return $content; 

  }

  global $post;
  $post_type = get_post_type( $post->ID );

  $option_post_types = sanitize_option( 'dkpdf_pdfbutton_post_types', get_option( 'dkpdf_pdfbutton_post_types', array() ) );

  // TODO button checkboxes? 
  if ( is_archive() || is_front_page() || is_home() ) { return $content; }

  // return content if not checked
  if( $option_post_types ) { 

      if ( ! in_array( get_post_type( $post ), $option_post_types ) ) {

        return $content;

      }

  }

  if( $option_post_types ) { 

      if ( in_array( get_post_type( $post ), $option_post_types ) ) {

        $c = $content;

        $pdfbutton_position = sanitize_option( 'dkpdf_pdfbutton_position', get_option( 'dkpdf_pdfbutton_position', 'before' ) );

        $template = new DKPDF_Template_Loader;

        if( $pdfbutton_position ) {

            if( $pdfbutton_position == 'before' ) {

              ob_start();

              $content = $template->get_template_part( 'dkpdf-button' );

              return ob_get_clean() . $c;


            } else if ( $pdfbutton_position == 'after' ) {

              ob_start();

              $content = $template->get_template_part( 'dkpdf-button' );

              return $c . ob_get_clean();

            }

        }

      }

  } else {

    return $content;

  }

}

add_filter( 'the_content', 'dkpdf_display_pdf_button' );

/**
* output the pdf
*/
function dkpdf_output_pdf( $query ) {

  // TODO sanitize validate...
  $pdf = get_query_var( 'pdf' );

  if( $pdf ) {

      include('mpdf60/mpdf.php');

      //$mpdf = new mPDF();
      $mpdf = new mPDF( '','A4','','','15','15','28','18' ); // TODO understand all params :)   

      // header 
      $pdf_header_html = dkpdf_get_template( 'dkpdf-header' );
      $mpdf->SetHTMLHeader( $pdf_header_html );

      // footer      
      $pdf_footer_html = dkpdf_get_template( 'dkpdf-footer' );
      $mpdf->SetHTMLFooter( $pdf_footer_html );

      $mpdf->WriteHTML( dkpdf_get_template( 'dkpdf-index' ) );    

      // action to do (open or download)  

      $pdfbutton_action = sanitize_option( 'dkpdf_pdfbutton_action', get_option( 'dkpdf_pdfbutton_action', 'open' ) );

      if( $pdfbutton_action == 'open') {

        $mpdf->Output();

      } else {

        global $post;
        $mpdf->Output( get_the_title( $post->ID ).'.pdf', 'D' );

      }

      exit;

  } 

}

add_action( 'wp', 'dkpdf_output_pdf' );

/**
* returs a template
* @param string template name
*/ 
function dkpdf_get_template( $template_name ) {
    
    $template = new DKPDF_Template_Loader;

    ob_start();
    $template->get_template_part( $template_name );
    return ob_get_clean();   

}

/**
* returns an array of active post, page, attachment and custom post types
* @return array 
*/
function dkpdf_get_post_types() {
        
    $args = array(
       'public'   => true,
       '_builtin' => false
    );

    $post_types = get_post_types( $args ); 
    $post_arr = array( 'post' => 'post', 'page' => 'page', 'attachment' => 'attachment' );

    foreach ( $post_types  as $post_type ) {

      $arr = array( $post_type => $post_type );
      $post_arr += $arr;

    }
    
    $post_arr = apply_filters( 'dkpdf' . '_posts_arr', $post_arr );

    return $post_arr;

}

/**
* set query_vars
*/
function dkpdf_set_query_vars( $query_vars ) {

  $query_vars[] = 'pdf';

  return $query_vars;

}

add_filter( 'query_vars', 'dkpdf_set_query_vars' );





